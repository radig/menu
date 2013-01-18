<?php
App::uses('MenuBuilderComponent', 'Menu.Controller/Component');

/**
 * MenuBuilderComponent Test Case
 *
 */
class MenuBuilderComponentTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.menu.menu'
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$Collection = new ComponentCollection();
		$this->MenuBuilder = new MenuBuilderComponent($Collection);

		Configure::write('Menu.Main', Configure::write('Radig.Menu.Main', array(
				'title' => 'Item 1',
				'plugin' => null,
				'controller' => null,
				'action' => null,
				'childs' => array(
					array(
						'title' => 'Subitem 1',
						'plugin' => 'subitem',
						'controller' => 'subitems',
						'action' => 'index',
						'admin' => true
					),
					array(
						'title' => 'Subitem 2',
						'plugin' => 'subitem',
						'controller' => 'subitems',
						'action' => 'add',
						'admin' => false
					),
					array(
						'title' => 'Subitem 1',
						'plugin' => 'subitem',
						'controller' => 'subitems',
						'action' => 'other',
					),
				)
			),
			array(
				'title' => 'Item 2',
				'plugin' => null,
				'controller' => null,
				'action' => null,
				'childs' => array(
					array(
						'title' => 'Subitem 2.1',
						'controller' => 'subitem2',
					),
					array(
						'title' => 'Subitem 2.2',
						'controller' => 'subitems2',
						'action' => 'index',
						'panel' => true,
						'named1' => 'test'
					)
				)
			)
		));
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->MenuBuilder);

		parent::tearDown();
	}

/**
 * testBuild method
 *
 * @return void
 */
	public function testBuild() {
	}
}
