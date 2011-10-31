<?php
class MenuHelper extends AppHelper
{
	public $helpers = array('Html');
	
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
		$out = '<li' . ($isRoot ? ' class="topLevel"' : '') . '>';
		
		$url = array();
		
		$url['prefix'] = empty($nodes['prefix']) ? false : $nodes['prefix'];
		
		$url['plugin'] = empty($nodes['plugin']) ? false : $nodes['plugin'];
		
		$url['controller'] = empty($nodes['controller']) ? false : $nodes['controller'];
		
		$url['action'] = empty($nodes['action']) ? false : $nodes['action'];
		
		$nodes['class'] = !isset($nodes['class']) ? '' : $nodes['class'];
		
		if(empty($url['controller']))
			$url = '#';
			
		$out .= $this->Html->link(__($nodes['title'], true), $url, array('class' => $nodes['class'], 'title' => $nodes['title']));
		
		if(isset($nodes['childs']) && !empty($nodes['childs']))
		{
			$out .= '<ul>';
			
			foreach($nodes['childs'] as $child)
			{
				$out .= $this->deepVisitor($child, false);
			}
			
			$out .= '</ul>';
		}
		
		$out .= '</li>';
		
		return $out;
	}
}