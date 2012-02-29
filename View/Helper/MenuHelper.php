<?php
/**
 * Helper que constrói o código HTML de navegação
 * para o menu gerado.
 * 
 * @package		radig.Menu.View.Helper
 * @copyright		Radig Soluções em TI
 * @author			Radig Dev Team - suporte@radig.com.br
 * @version		2.0
 * @license		Vide arquivo LICENCA incluído no pacote
 * @link			http://radig.com.br
 */
class MenuHelper extends AppHelper
{
	public $helpers = array('Html');
	
	public $settings = array(
		'firstLevelClass' => 'dropdown',
		'activeItemClass' => 'active',
	);
	
	/**
	 * Método recursive que constrói um menu aninhado com submenus
	 * 
	 * @param $item
	 * @param $label
	 * 
	 * @return $out string
	 */
	public function printItem($item, $label)
	{
		$out = '<li>';
		
		if(isset($item['action']))
		{
			$out .= $this->Html->link($label, array('plugin' => $item['plugin'], 'controller' => $item['controller'], 'action' => $item['action'], isset($item['group_id']) ?  $item['group_id'] : '', $this->params['prefix'] => isset($item['admin']) ? $item['admin'] : true ), array('class' => ''));
		}
		else 
		{
			$out .= $this->Html->link($label,'#');
		}
		
		if(!isset($item['action']))
		{
			$out .= '<ul >';
			foreach($item as $title => $itens)
			{
				$out .= $this->printItem($itens, $title);
			}
			
			$out .= '</ul>';
		}

		$out .= '</li>';
		return $out;
	}
	
	/**
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
		$img = '../img/' . $img;
		
		if($confirmation === null)
			$button = $this->Html->link(
				$this->Html->image($img, array('alt' => $title)),
				$url,
				array('escape' => false, 'title' => $title, 'class' => 'jbutton')
			);
		else
			$button = $this->Html->link(
				$this->Html->image($img, array('alt' => $title)),
				$url,
				array('escape' => false, 'title' => $title, 'class' => 'jbutton'),
				__($confirmation, true)
			);
			
			
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
		
		if(empty($items))
			return $menu;
		
		foreach($items as $item)
		{
			$menu .= $this->deepVisitor($item);
		}
		
		return $menu;
	}
	
	/**
	 * Constroí e retorna um menu completo
	 * 
	 * @param array $items Itens do menu em um array aninhado (estilo árvore)
	 * 
	 * @return string $menu
	 */
	public function renderSide($items)
	{
		$menu = '';
		$menuRelation = Configure::read('Radig.Menu.Relation');
		
		$arrayItem = $this->getRelated();

		if(empty($arrayItem))
			return $menu;

		foreach($arrayItem as $key)
		{
			$menu .= $this->deepVisitorSide($items[$key]);
		}
		
		
		return $menu;
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
		$out = '<li class=" ';
		
		if($isRoot && isset($nodes['childs']) && !empty($nodes['childs']))
		{
			$out .= $this->settings['firstLevelClass'];
		}
		
		$url = array();
		
		$url['prefix'] = empty($nodes['prefix']) ? false : $nodes['prefix'];
		
		$url['plugin'] = empty($nodes['plugin']) ? false : $nodes['plugin'];
		
		$url['controller'] = empty($nodes['controller']) ? false : $nodes['controller'];
		
		$url['action'] = empty($nodes['action']) ? false : $nodes['action'];
		
		$nodes['class'] = !isset($nodes['class']) ? '' : $nodes['class'];
		
		$nodes['icon'] = !isset($nodes['icon']) ? '' : $nodes['icon'];

		if($this->hasUrl($nodes))
		{
			$out .= ' ' . $this->settings['activeItemClass'];
		}
		
		$out .= '">';
		
		if(empty($url['controller']))
			$url = '#';
		
		if(!isset($nodes['childs']) && empty($nodes['childs']))
			$out .= $this->Html->link(
				(!empty($nodes['icon'])?$this->Html->tag('i', '', array('class' => $nodes['icon'].' icon-white')):'') . __($nodes['title'], true),
				$url, 
				array('class' => $nodes['class'], 'title' => $nodes['title'], 'escape' => false)
			);
		
		
		if(isset($nodes['childs']) && !empty($nodes['childs']))
		{
			$out .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.__($nodes['title'], true).'<b class="caret"></b></a>';
			$out .= '<ul class="dropdown-menu">';
			
			foreach($nodes['childs'] as $child)
			{
				$out .= $this->deepVisitor($child, false);
			}
			
			$out .= '</ul>';
		}
		
		$out .= '</li>';
		
		return $out;
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
	protected function deepVisitorSide($nodes)
	{
		$out = '<li class=" ';
		
		if(isset($nodes['childs']) && !empty($nodes['childs']))
		{
			$out .= 'nav-header';
		}
		
		$url = array();
		
		$url['prefix'] = empty($nodes['prefix']) ? false : $nodes['prefix'];
		
		$url['plugin'] = empty($nodes['plugin']) ? false : $nodes['plugin'];
		
		$url['controller'] = empty($nodes['controller']) ? false : $nodes['controller'];
		
		$url['action'] = empty($nodes['action']) ? false : $nodes['action'];
		
		$nodes['class'] = !isset($nodes['class']) ? '' : $nodes['class'];
		
		$out .= '">';
		
		if(empty($url['controller']))
			$out .= __($nodes['title'], true);
		else
			$out .= $this->Html->link(__($nodes['title'], true), $url, array('class' => $nodes['class'], 'title' => $nodes['title']));
		
		$out .= '</li>';
		
		if(isset($nodes['childs']) && !empty($nodes['childs']))
		{
			foreach($nodes['childs'] as $child)
			{
				$out .= $this->deepVisitor($child, false);
			}
		}
		
		return $out;
	}

	/**
	 * Percorre os filhos, procura por algum item ativo
	 * 
	 * @param array $nodes
	 * 
	 * @return boolean Item está ativo
	 */
	protected function hasUrl($nodes)
	{
		$url = array();
		
		$url['prefix'] = empty($nodes['prefix']) ? false : $nodes['prefix'];
		
		$url['plugin'] = empty($nodes['plugin']) ? false : $nodes['plugin'];
		
		$url['controller'] = empty($nodes['controller']) ? false : $nodes['controller'];
		
		$url['action'] = empty($nodes['action']) ? false : $nodes['action'];
		
		$nodes['class'] = !isset($nodes['class']) ? '' : $nodes['class'];
		
		if($this->request->here == $this->url($url))
		{
			return true;
		}
		if(isset($nodes['childs']) && !empty($nodes['childs']))
		{
			foreach($nodes['childs'] as $child)
			{
				return $this->hasUrl($child);
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
		
		if(!empty($this->request->params['plugin']))
			$here .= $this->request->params['plugin'].'/';
		
		if(!empty($this->request->params['controller']))
			$here .= $this->request->params['controller'].'/';
		
		if(!empty($this->request->params['action']))
			$here .= $this->request->params['action'];
		
		return $here;
	}

	/**
	 * Retorna relação de menu/sidebar menu, ou nulo caso não exista
	 * 
	 * @return array Relação de Sidebar Menus
	 */
	public function getRelated()
	{
		$menuRelation = Configure::read('Radig.Menu.Relation');
		
		if(isset($menuRelation[$this->getHere()]))
			return $menuRelation[$this->getHere()];
		
		return null;
	}
}