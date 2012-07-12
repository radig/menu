<?php
App::uses('AppHelper', 'View/Helper');
/**
 * Helper que constrói o código HTML de navegação
 * para o menu gerado.
 *
 * @package		radig.Menu.View.Helper
 * @copyright	Radig Soluções em TI
 * @author		Radig Dev Team - suporte@radig.com.br
 * @version		3.1
 * @license		Vide arquivo LICENCA incluído no pacote
 * @link		http://radig.com.br
 */
class MenuHelper extends AppHelper
{
	public $helpers = array('Html');

	public $settings = array(
		'firstLevelClass' => 'dropdown',
		'activeItemClass' => 'active',
	);

	/**
	 * Atualiza configurações do helper
	 *
	 * @param  array $newSettings
	 *
	 * @return void
	 */
	public function settings($newSettings) {
		$this->settings += $newSettings;
	}

	/**
	 * Gera um link estilisado como botão, que pode ou não emitir
	 * uma mensagem de confirmação
	 *
	 * @param string $title
	 * @param string $img
	 * @param array $url
	 * @param string|null $confirmation
	 *
	 * @return string
	 */
	public function button($title, $img, $url, $confirmation = null)
	{
		$title = __($title, true);
		$img = "/img/{$img}";

		if($confirmation === null) {
			$button = $this->Html->link(
				$this->Html->image($img, array('alt' => $title)),
				$url,
				array('escape' => false, 'title' => $title, 'class' => 'btn')
			);
		} else {
			$button = $this->Html->link(
				$this->Html->image($img, array('alt' => $title)),
				$url,
				array('escape' => false, 'title' => $title, 'class' => 'btn'),
				__($confirmation, true)
			);
		}

		return $button;
	}

	/**
	 * Constroí e retorna um menu completo
	 *
	 * @param array $items Itens do menu em um array aninhado (estilo árvore)
	 *
	 * @return string $menu
	 */
	public function render($items)
	{
		$menu = '';

		if(empty($items)) {
			return $menu;
		}

		$menu = '<ul>';

		foreach($items as $item) {
			$menu .= $this->deepVisitor($item);
		}

		$menu .= '</ul>';

		return $menu;
	}

	/**
	 * Constroí e retorna um menu completo
	 *
	 * @param array $items Itens do menu em um array aninhado (estilo árvore)
	 * @param array $relations Array com cada item do menu e seus itens relacionados
	 *
	 * @return string $menu
	 */
	public function contextual($items, $relations)
	{
		$menu = '';
		$arrayItem = $this->getRelated($relations);

		if(empty($arrayItem)) {
			return $menu;
		}

		if($arrayItem === true) {
			$arrayItem = array_keys($items);
		}

		foreach($arrayItem as $key) {
			$menu .= $this->deepVisitor($items[$key]);
		}

		return $menu;
	}

	/**
	 * Retorna relação de menu/sidebar menu, ou nulo caso não exista
	 *
	 * @param array $relations Array com cada item do menu (sua url) e seus itens relacionados (seus ids)
	 *
	 * @return array Relação de Sidebar Menus
	 */
	public function getRelateds($relations)
	{
		if(isset($relations[$this->getHere()])) {
			return $relations[$this->getHere()];
		}

		if(isset($relations['*'])) {
			return true;
		}

		return null;
	}

	/**
	 * Constroí uma lista aninhada recursivamente, a partir de um
	 * array hierárquico
	 *
	 * @param array $nodes
	 * @param bool $isRoot
	 *
	 * @return string $out Lista aninhada
	 */
	protected function deepVisitor($nodes, $isRoot = true)
	{
		$out = '<li class="';

		if($isRoot && isset($nodes['childs']) && !empty($nodes['childs'])) {
			$out .= $this->settings['firstLevelClass'];
		}

		if($this->isHere($nodes)) {
			$out .= ' ' . $this->settings['activeItemClass'];
		}

		$out .= '">';

		$url = $this->buildUrl($nodes);

		$attrs = array('title' => __($nodes['title']));
		if(isset($nodes['attrs'])) {
			$attrs += $nodes['attrs'];
		}

		if(isset($nodes['icon']) && !empty($nodes['icon'])) {
			$attrs['escape'] = false;
			$attrs['title'] = $this->Html->tag('i', '', array('class' => $nodes['icon'])) . $attrs['title'];
		}

		if(!isset($nodes['childs']) && empty($nodes['childs'])) {
			$out .= $this->Html->link($attrs['title'], $url, $attrs);

		} else {
			$out .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'. $attrs['title'] .'<b class="caret"></b></a>';
			$out .= '<ul class="dropdown-menu">';

			foreach($nodes['childs'] as $child) {
				$out .= $this->deepVisitor($child, false);
			}

			$out .= '</ul>';
		}

		$out .= '</li>';

		return $out;
	}

	/**
	 * Percorre os filhos, procura por algum item ativo para marcar
	 * o pai como ativo também
	 *
	 * @param array $nodes
	 *
	 * @return boolean Item está ativo
	 */
	protected function isHere($nodes)
	{
		$url = $this->buildUrl($nodes);

		if($this->request->here == $this->url($url)) {
			return true;
		}

		if(isset($nodes['childs']) && !empty($nodes['childs'])) {
			foreach($nodes['childs'] as $child) {
				return $this->isHere($child);
			}
		}

		return false;
	}

	/**
	 * Retorna rota completa do ponto em que a função foi chamada
	 *
	 * @return string Rota
	 */
	protected function getHere()
	{
		$here = '';

		if(!empty($this->request->plugin)) {
			$here .= strtolower($this->request->plugin) . '/';
		}

		if(!empty($this->request->controller)) {
			$here .= strtolower($this->request->controller) . '/';
		}

		if(!empty($this->request->action)) {
			$here .= $this->request->action;
		}

		return $here;
	}

	/**
	 * Recebe um array completo de URL e outras opções e monta
	 * um array apenas com itens relevantes para a URL
	 *
	 * @param  array $fragments Um array com fragmentos da URL junto a outras opções
	 * @return array|string $url URL válida para CakePHP
	 */
	protected function buildUrl($fragments)
	{
		$url = array();

		$url['plugin']     = $this->getFragment($fragments, 'plugin');
		unset($fragments['plugin']);

		$url['controller'] = $this->getFragment($fragments, 'controller');
		unset($fragments['controller']);

		$url['action']     = $this->getFragment($fragments, 'action');
		unset($fragments['action']);

		if(isset($fragments['params'])) {
			foreach($fragments['params'] as $key => $param) {
				if(is_numeric($key)) {
					array_push($url, $param);
					continue;
				}

				$url[$key] = $param;
			}

			unset($fragments['params']);
		}

		if(empty($url['plugin']) && empty($url['controller']) && empty($url['action'])) {
			$url = '#';
		}

		return $url;
	}

	/**
	 * Método auxiliar para recuperar uma posição do array $arr
	 * se ela estiver setada e não estiver vazia. Devolve $default
	 * caso contrário.
	 *
	 * @param  array   $arr
	 * @param  string  $name
	 * @param  boolean $default
	 * @return mixed
	 */
	protected function getFragment($arr, $name, $default = false)
	{
		return (isset($arr[$name]) && !empty($arr[$name])) ? $arr[$name] : $default;
	}
}