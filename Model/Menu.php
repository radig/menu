<?php
/**
 * Modelo que representa as entradas do menu persistida em BD.
 *
 * @package		radig.Menu.Model
 * @copyright		Radig Soluções em TI
 * @author			Radig Dev Team - suporte@radig.com.br
 * @version		2.0
 * @license		Vide arquivo LICENCA incluído no pacote
 * @link			http://radig.com.br
 */
class Menu extends AppModel {
	public $name = 'Menu';
	public $displayField = 'title';

	public $belongsTo = array(
		'ParentMenu' => array(
			'className' => 'Menu',
			'foreignKey' => 'parent_id'
		)
	);

	public $hasMany = array(
		'ChildMenu' => array(
			'className' => 'Menu',
			'foreignKey' => 'parent_id'
		)
	);
}