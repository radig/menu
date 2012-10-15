<?php
App::uses('Component', 'Controller');
/**
 * Componente que constrói um Menu baseado nas permissões
 * do usuário.
 *
 * @package		radig.Menu.Controller.Component
 * @copyright	Radig Soluções em TI
 * @author		Radig Dev Team - suporte@radig.com.br
 * @version		3.1
 * @license		Vide arquivo LICENCA incluído no pacote
 * @link		http://radig.com.br
 */
class MenuBuilderComponent extends Component
{
	/**
	 * Referência para o controlador que está em execução
	 *
	 * @var Controller
	 */
	protected $Controller = null;

	/**
	 * Componentes utilizados
	 * @var array
	 */
	public $components = array('Auth', 'Acl', 'Session');

	protected  $_user = null;

	private $_defaultSettings = array(
		'rootNode' => 'controllers',
		'cacheConfig' => 'default',
		'aroNamePrefix' => '_'
	);

	private $_acoTree = array();

	/**
	 * Construtor padrão
	 *
	 * @param ComponentCollection $collection
	 * @param array $settings
	 */
	public function __construct(ComponentCollection $collection, $settings = array())
	{
		parent::__construct($collection, $settings);

		$this->settings = Set::merge($this->_defaultSettings, $settings);
	}

	/**
	 * Inicialização do componente
	 *
	 * @param Controller $controller
	 * @param array $settings
	 */
	public function initialize(Controller $controller)
	{
		$this->Controller = $controller;

		$this->_user = $this->Auth->user();
	}

	/**
	 * Callback invocado imediatamente antes do Controller::beforeFilter()
	 *
	 * @param Controller $controller
	 */
	public function startup(Controller $controller)
	{
		$this->Controller = $controller;
	}

	/**
	* Monta o menu para as ações permitidas
	*
	* @return null
	*/
	public function build($items, $key = 'Main', $useCache = true)
	{
		$cached = null;
		if($useCache) {
			$cached = Cache::read("Menu.{$this->_user['id']}.{$key}", $this->settings['cacheConfig']);
		}

		if($useCache && !empty($cached)) {
			return $cached;
		}

		$this->_setupAcoTree($useCache);
		$menu = $this->_build($items);

		if($useCache) {
			Cache::write("Menu.{$this->_user['id']}.{$key}", $menu, $this->settings['cacheConfig']);
		}

		return $menu;
	}

	/**
	* Recupera árvore de Acos
	*
	* @param bool $useCache
	* @return array $acoTree
	*/
	protected function _setupAcoTree($useCache = true)
	{
		if(empty($this->_acoTree) && $useCache) {
			// primeiramente verifica por cache contendo a arvore, se existir, a utiliza
			$cached = Cache::read('AcoTree.' . $this->_user['id'], $this->settings['cacheConfig']);

			if($cached) {
				$this->_acoTree = $cached;
				return $cached;
			}
		}

		$this->_acoTree = $this->__buildAcoTree();

		if($useCache) {
			Cache::write('AcoTree.' . $this->_user['id'], $this->_acoTree, $this->settings['cacheConfig']);
		}

		return $this->_acoTree;
	}

	/**
	 * Constroí menu utilizando como entrada um array com elementos em cascata (recursivos)
	 *
	 * @param array $items
	 * @return array $menu
	 */
	protected function _build($items = array())
	{
		$menu = $this->_deepCheck($items);

		// varre o menu em busca de pais sem filho e sem ação (botão estático)
		foreach($menu as $key => $button) {
			if(!isset($button['childs']) && empty($button['controller']) && empty($button['action'])) {
				unset($menu[$key]);
			}
		}

		return $menu;
	}

	/**
	 * Verificação permissão para determinado item de menu/ação
	 * recursivamente.
	 *
	 * @param array $items
	 * @return array $items
	 */
	protected function _deepCheck(&$items)
	{
		if(!is_array($items)) {
			return array();
		}

		foreach($items as $k => $item) {
			if($this->_checkMenuNode($item)) {
				if(isset($item['childs']) && !empty($item['childs'])) {
					$item['childs'] = $this->_deepCheck($item['childs']);

					if(empty($item['childs'])) {
						unset($items[$k]);
					}
				}

				continue;
			}

			unset($items[$k]);
		}

		return $items;
	}

	/**
	 * Checa as permissões para o usuário para uma determinada ação/nó
	 *
	 * @param array $menu Menu padrão com URL do CakePHP
	 * @return boolean TRUE se o usuário logado tiver permissão para
	 * a ação, FALSE caso contrário
	 */
	protected function _checkMenuNode($menu)
	{
		$aco = 'controllers/';

		if(!empty($menu['plugin'])) {
			$aco .= Inflector::camelize($menu['plugin']) . '/';
		}

		if(!empty($menu['controller'])) {
			$aco .= Inflector::camelize($menu['controller']) . '/';
		}

		if(!empty($menu['action'])) {
			$aco .= $menu['action'];
		}

		// Caso o item de menu não tenha url, ele é autorizado por padrão
		if($aco === 'controllers/') {
			return true;
		}

		$aro = $this->settings['aroNamePrefix'] . $this->_user['username'];

		return $this->Acl->check($aro, $aco);
	}

	/**
	 * Busca em profundidade, recursiva
	 *
	 * @param array $nodes
	 * @param string $term
	 */
	protected function _deepSearch($nodes, $term)
	{
		if($nodes['alias'] == $term) {
			return (bool)$nodes['authorized'];
		}

		if(isset($nodes['childs'])) {
			$authorized = true;

			foreach($nodes['childs'] as $child) {
				$authorized = $authorized && $this->_deepSearch($child, $term);
			}

			return $authorized;
		}

		return false;
	}

	/**
	 * Constroi e retorna uma árvore de ações juntamente com permissão de
	 * acesso para o usuário logado.
	 *
	 * @return array $acoTree
	 */
	private function __buildAcoTree()
	{
		$indexes = array();
		$tree = array();
		$permissions = array();

		$aroTree = array_reverse($this->Acl->Aro->node(array('model' => 'User', 'foreign_key' => $this->_user['id'])));

		foreach($aroTree as $node) {
			$aro = $this->Acl->Aro->find('first', array(
					'conditions' => array(
						'Aro.id' => $node['Aro']['id']
					),
					'contain' => array('Aco')
				)
			);

			foreach($aro['Aco'] as $aco) {
				$permissions[$aco['id']] = true;
			}
		}

		$acoTree = $this->Acl->Aco->find('all', array(
				'contain' => array(),
				'order' => 'Aco.lft'
			)
		);

		foreach($acoTree as $key => $acoNode) {
			$aco = $acoNode['Aco'];

			if(empty($aco['parent_id'])) {
				$tree[$aco['id']] = $aco;
				$indexes[$aco['id']] = &$tree[$aco['id']];

			} else if(!empty($indexes[$aco['parent_id']])) {
				$indexes[$aco['parent_id']]['childs'][$key] = $aco;
				$indexes[$aco['id']] = &$indexes[$aco['parent_id']]['childs'][$key];
			}

			$node = $aco;
			while(true) {
				if(!isset($permissions[$node['id']]) && !empty($node['parent_id'])) {

					if(isset($indexes[$node['parent_id']])) {
						$node = $indexes[$node['parent_id']];
						continue;
					}
				}
				break;
			}

			if(isset($permissions[$node['id']])) {
				$permissions[$aco['id']] = $permissions[$node['id']];
			} else {
				$permissions[$aco['id']] = false;
			}

			$indexes[$aco['id']]['authorized'] = $permissions[$aco['id']];
		}

		foreach($indexes as $id => $node) {
			if($permissions[$id]) {
				$aux = $node;

				while(true) {
					$indexes[$aux['id']]['authorized'] = true;

					if(!empty($aux['parent_id'])) {
						$aux = $indexes[$aux['parent_id']];
						continue;
					}

					break;
				}
			}
		}

		return $tree;
	}
}