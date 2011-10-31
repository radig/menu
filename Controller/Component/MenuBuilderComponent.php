<?php
/**
 * 
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
		'rootNode' => 'controllers'
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
	public function initialize(&$controller)
	{
		$this->Controller =& $controller;
		
		$this->_user = $this->Auth->user();
	}
	
	/**
	 * Callback invocado imediatamente antes do Controller::beforeFilter()
	 * 
	 * @param Controller $controller
	 */
	public function startup(&$controller)
	{
		$this->Controller =& $controller;
	}
	
	/**
	* Monta o menu para as ações permitidas
	*
	* @return null
	*/
	public function build()
	{
		$menu = array();
		
		if(!Cache::read("User.{$this->_user['id']}.Menu"))
		{
			$this->_setupAcoTree();
			$menu = $this->_build(Configure::read('Radig.Menu.Main'));
		}
		else
		{
			$menu = Cache::read("User.{$this->_user['id']}.Menu");
		}
	
		return $menu;
	}
	
	/**
	 * Monta o menu de configurações com ações permitidas
	 *
	 * @return null
	 */
	public function buildConfigurationMenu()
	{
		$menu = array();
	
		if(!Cache::read("User.{$this->_user['id']}.ConfigMenu"))
		{
			$rawMenu = Configure::read('ConfigurationMenu');
	
			$profile = $this->profileAction;
	
			$profile['title'] = 'Meus Dados';
			$profile['class'] = 'button-app-profile button user-button';
	
			$rawMenu[] = $profile;

			$this->_setupAcoTree();
			$menu = $this->_build($rawMenu, 'ConfigMenu');
		}
		else
		{
			$menu = Cache::read("User.{$this->_user['id']}.ConfigMenu");
		}
	
		return $menu;
	}
	
	/**
	* Recupera árvore de Acos
	*
	* @return array $acoTree
	*/
	protected function _setupAcoTree()
	{
		if(empty($this->_acoTree))
		{
			// primeiramente verifica por cache contendo a arvore, se existir, a utiliza
			$tree = Cache::read('User.' . $this->_user['id'] . '.Aco');
				
			if($tree !== false)
			{
				$this->_acoTree = $tree;
			}
			else
			{
				$this->_acoTree = $this->__buildAcoTree();
	
				// faz cache da árvore
				Cache::write('User.' . $this->_user['id'] . '.Aco', $tree);
			}
		}
	
		return $this->_acoTree;
	}
	
	/**
	 * Constroí menu utilizando como entrada um array com elementos em cascata (recursivos)
	 * 
	 * @param array $items
	 * @param array $cacheKey
	 */
	protected function _build($items = array(), $cacheKey = 'Menu')
	{
		$menu = $this->_deepCheck($items);
		
		// varre o menu em busca de pais sem filho e sem ação (botão estético vazio)
		foreach($menu as $key => $button)
		{
			if(!isset($button['childs']) && empty($button['controller']) && empty($button['action']))
			{
				unset($menu[$key]);
			}
		}
		
		Cache::write("User.{$this->_user['id']}.{$cacheKey}", $menu);
		
		return $menu;
	}
	
	/**
	 * Verificação permissão para determinado item de menu/ação
	 * recursivamente.
	 * 
	 * @param array $items
	 */
	protected function _deepCheck(&$items)
	{
		if(!is_array($items))
			return array();
			
		foreach($items as $k => $item)
		{
			if($this->_checkMenuNode($item))
			{
				if(isset($item['childs']) && !empty($item['childs']))
					$item['childs'] = $this->_deepCheck($item['childs']);
			}
			else
				unset($items[$k]);
		}
			
		return $items;
	}
	
	/**
	 * 
	 */
	protected function _checkMenuNode($menu)
	{
		$aco = '';
		
		if(!empty($menu['plugin']))
			$aco .= Inflector::camelize($menu['plugin']);
			
		if(!empty($aco))
			$aco .= '/';
			
		if(!empty($menu['controller']))
			$aco .= Inflector::camelize($menu['controller']);
		
		if(!empty($aco))
			$aco .= '/';
			
		if(!empty($menu['action']))
			$aco .= $menu['action'];
		
		// Caso o item de menu não tenha url, ele é autorizado por padrão
		if(empty($aco))
			return true;
			
		$aro = '_' . $this->_user['username'];
		
		return $this->Acl->check($aro, $aco);
	}
	
	/**
	 * Busca e retorna a permissão para um nó na
	 * árvore de acos
	 * 
	 * @param string $alias
	 */
	protected function _getAcoNode($alias = '')
	{
		if(empty($this->_acoTree))
			return false;
			
		$alias = Inflector::camelize($alias);
		
		foreach($this->_acoTree[$this->settings['rootNode']] as $node)
		{
			return $this->_deepSearch($node, $term);
		}
	}
	
	/**
	 * Busca em profundidade, recursiva
	 * 
	 * @param array $nodes
	 * @param string $term
	 */
	protected function _deepSearch($nodes, $term)
	{
		if($nodes['alias'] == $term)
		{
			return (bool)$nodes['authorized'];
		}
		
		if(isset($nodes['childs']))
		{
			$authorized = true;
			
			foreach($nodes['childs'] as $child)
			{
				$authorized = $authorized && $this->_deepSearch($child, $term);
			}
			
			return $authorized;
		}
		
		return false;
	}
	
	/**
	 * Constroi e retorna uma árvore de ações juntamente com permissão de
	 * acesso para o usuário logado.
	 */
	private function __buildAcoTree()
	{
		$indexes = array();
		$tree = array();
		$permissions = array();
	
		// Recupera AROs relacionados ao usuário, na ordem Geral -> Específico
		$aroTree = array_reverse($this->Acl->Aro->node(array('model' => 'User', 'foreign_key' => $this->_user['id'])));
	
		// Percorre lista de AROs relacionadas ao usuário
		foreach($aroTree as $node)
		{
			// Recupera os ACOs para cada ARO
			$aro = $this->Acl->Aro->find('first', array(
					'conditions' => array(
					'Aro.id' => $node['Aro']['id']
				),
					'contain' => array('Aco')
				)
			);
	
			// Para cada ACO, computa as permissões definidas
			foreach($aro['Aco'] as $aco)
			{
				$permissions[$aco['id']] = true;
			}
		}
	
		// inicia construção da árvore de ACOs
		$acoTree = $this->Acl->Aco->find('all', array(
				'contain' => array(),
				'order' => 'Aco.lft'
			)
		);
	
		foreach($acoTree as $key => $acoNode)
		{
			$aco = $acoNode['Aco'];
	
			if(empty($aco['parent_id']))
			{
				$tree[$aco['id']] = $aco;
	
				$indexes[$aco['id']] = &$tree[$aco['id']];
			}
			// verifica se o nó pai já foi preenchido
			else if(!empty($indexes[$aco['parent_id']]))
			{
				$indexes[$aco['parent_id']]['childs'][$key] = $aco;
	
				$indexes[$aco['id']] = &$indexes[$aco['parent_id']]['childs'][$key];
			}
	
			// identifica permissão para o nó encontrado
			$node = $aco;
	
			while(true)
			{
				// caso não possua permissão atrelada, mas seja um nó filho
				if(!isset($permissions[$node['id']]) && !empty($node['parent_id']))
				{
					// caso haja registro do nó pai
					if(isset($indexes[$node['parent_id']]))
					{
						// atualiza o nó corrente para o nó pai, para então verificar suas permissões
						$node = $indexes[$node['parent_id']];
	
						continue;
					}
				}
				break;
			}
	
			if(isset($permissions[$node['id']]))
			{
				$permissions[$aco['id']] = $permissions[$node['id']];
			}
			else
			{
				$permissions[$aco['id']] = false;
			}
	
			// associa permissão ao nó
			$indexes[$aco['id']]['authorized'] = $permissions[$aco['id']];
		}
	
		foreach($indexes as $id => $node)
		{
			// se autorização for true, repassa para seus 'ancestrais'
			if($permissions[$id])
			{
				$aux = $node;
	
				while(true)
				{
					$indexes[$aux['id']]['authorized'] = true;
	
					if(!empty($aux['parent_id']))
					{
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