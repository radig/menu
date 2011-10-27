<?php
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