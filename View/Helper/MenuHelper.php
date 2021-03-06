<?php
App::uses('AppHelper', 'View/Helper');
/**
 * Helper que constrói o código HTML de navegação
 * para o menu gerado.
 *
 * @package     radig.Menu.View.Helper
 * @copyright   Radig Soluções em TI
 * @author      Radig Dev Team - suporte@radig.com.br
 * @license     Vide arquivo LICENCA incluído no pacote
 * @link        http://radig.com.br
 */
class MenuHelper extends AppHelper
{
    public $helpers = array('Html');

    public $settings = array(
        'useCaret'       => true, // se deve utilizar a seta indicando submenu
        'styles' => array(
            'container'        => 'menu',            // classe do elemento pai 'ul'
            'itemRoot'         => 'dropdown',        // classe do elemento 'li' que contém submenus (ul), aplicado apenas ao primeiro nível
            'itemWithChild'    => '',                // classe do elemento 'li' que contém submenus (ul)
            'itemActiveClass'  => 'active',          // classe aplicada ao 'li' que contém o link ativo
            'submenuLink'      => 'dropdown-toggle', // classe do elemento 'a' quando o mesmo contém submenus (ul)
            'submenuContainer' => 'dropdown-menu',   // classe do elemento 'ul' de submenus
        )
    );

    /**
     * Guarda informação de que um item do menu
     * já foi marcado como ativo.
     * 
     * @var boolean
     */
    protected $_definedActive = false;

    /**
     * Atualiza configurações do helper
     *
     * @param  array $newSettings
     *
     * @return void
     */
    public function settings($newSettings) {
        $this->settings = Hash::merge($this->settings, $newSettings);
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
        $this->_definedActive = false;
        $menu = '';
        if (empty($items)) {
            return $menu;
        }

        $menu = '<ul class="' . $this->settings['styles']['container'] . '">';

        foreach ($items as $item) {
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
        $this->_definedActive = false;
        $menu = '';
        $arrayItem = $this->getRelated($relations);

        if (empty($arrayItem)) {
            return $menu;
        }

        if ($arrayItem === true) {
            $arrayItem = array_keys($items);
        }

        foreach ($arrayItem as $key) {
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
        if (isset($relations[$this->getHere()])) {
            return $relations[$this->getHere()];
        }

        if (isset($relations['*'])) {
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
        $itemAttrs = array('class' => '');
        if (isset($nodes['itemAttrs'])) {
            $itemAttrs = $nodes['itemAttrs'] + $itemAttrs;
            unset($nodes['itemsAttrs']);
        }

        $url = $this->buildUrl($nodes);

        if (isset($nodes['childs']) && !empty($nodes['childs'])) {
            $itemAttrs['class'] .= $isRoot && !empty($this->settings['styles']['itemRoot']) ? ' ' . $this->settings['styles']['itemRoot'] : '';
            $itemAttrs['class'] .= !empty($this->settings['styles']['itemWithChild']) ? ' ' . $this->settings['styles']['itemWithChild'] : '';
        }

        if (!empty($this->settings['styles']['itemActiveClass']) && !$this->_definedActive && $this->isHere($nodes, $url)) {
            $this->_definedActive = true;
            $itemAttrs['class'] .= ' ' . $this->settings['styles']['itemActiveClass'];
        }

        $attrs = array('title' => __($nodes['title']));
        if (isset($nodes['attrs'])) {
            $attrs += $nodes['attrs'];
        }

        $title = $attrs['title'];
        if (isset($nodes['icon']) && !empty($nodes['icon'])) {
            $attrs['escape'] = false;
            $title = $this->Html->tag('i', '', array('class' => $nodes['icon'])) . ' ' . $attrs['title'];
        }

        if (!isset($nodes['childs']) || empty($nodes['childs'])) {
            $itemContent = ($url === false) ? $title : $this->Html->link($title, $url, $attrs);
            return $this->Html->tag('li', $itemContent, $itemAttrs);
        }

        $aFmt = '<a href="#" class="%s">%s %s</a>';
        $caret = $this->settings['useCaret'] ? '<b class="caret"></b>' : '';
        $itemContent = sprintf($aFmt, $this->settings['styles']['submenuLink'], $title, $caret);

        $subFmt = '<ul class="%s">%s</ul>';
        $subContent = '';
        foreach ($nodes['childs'] as $child) {
            $subContent .= $this->deepVisitor($child, false);
        }

        $itemContent .= sprintf($subFmt, $this->settings['styles']['submenuContainer'], $subContent);

        return $this->Html->tag('li', $itemContent, $itemAttrs);
    }

    /**
     * Percorre os filhos, procura por algum item ativo para marcar
     * o pai como ativo também
     *
     * @param array $nodes
     *
     * @return boolean Item está ativo
     */
    protected function isHere($nodes, $current = null)
    {
        if ($current === null) {
            $current = $this->buildUrl($nodes);
        }

        if (empty($current) || $current === '#') {
            return false;
        }

        $currentUrl = $this->url($current);

        if ($this->request->here == $currentUrl) {
            return true;
        }

        if (Configure::check('Menu.currentRoot') && Configure::read('Menu.currentRoot') == $currentUrl) {
            return true;
        }

        if (isset($nodes['childs']) && !empty($nodes['childs'])) {
            foreach ($nodes['childs'] as $child) {
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

        if (!empty($this->request->plugin)) {
            $here .= strtolower($this->request->plugin) . '/';
        }

        if (!empty($this->request->controller)) {
            $here .= strtolower($this->request->controller) . '/';
        }

        if (!empty($this->request->action)) {
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

        if (isset($fragments['url'])) {
            $fragments = $fragments['url'];
        }

        if (is_string($fragments) || $fragments === false) {
            return $fragments;
        }

        $url['plugin']     = $this->popFragment($fragments, 'plugin');
        $url['controller'] = $this->popFragment($fragments, 'controller');
        $url['action']     = $this->popFragment($fragments, 'action');

        if (isset($fragments['params'])) {
            foreach ($fragments['params'] as $key => $param) {
                if (is_numeric($key)) {
                    array_push($url, $param);
                    continue;
                }

                $url[$key] = $param;
            }

            unset($fragments['params']);
        }

        if (empty($url['plugin']) && empty($url['controller']) && empty($url['action'])) {
            $url = '#';
        }

        return $url;
    }

    /**
     * Método auxiliar para recupera e remove uma posição do array $arr
     * se ela estiver setada e não estiver vazia. Devolve $default
     * caso contrário.
     *
     * @param  array   $arr
     * @param  string  $name
     * @param  boolean $default
     * @return mixed
     */
    protected function popFragment(&$arr, $name, $default = false)
    {
        if (isset($arr[$name]) && !empty($arr[$name])) {
            $value = $arr[$name];
            unset($arr[$name]);
            return $value;
        }

        return $default;
    }
}
