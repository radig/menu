<?php
App::uses('MenuHelper', 'Menu.View/Helper');

/**
 * MenuHelper Test Case
 *
 */
class MenuHelperTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
    public function setUp() {
        parent::setUp();
        $View = new View();
        $this->Menu = new MenuHelper($View);
    }

/**
 * tearDown method
 *
 * @return void
 */
    public function tearDown() {
        unset($this->Menu);

        parent::tearDown();
    }

/**
 * testSettings method
 *
 * @return void
 */
    public function testSettings() {
    }

/**
 * testButton method
 *
 * @return void
 */
    public function testButton() {
    }

/**
 * testRender method
 *
 * @return void
 */
    public function testRender() {
    }

/**
 * testContextual method
 *
 * @return void
 */
    public function testContextual() {
    }

/**
 * testGetRelateds method
 *
 * @return void
 */
    public function testGetRelateds() {
    }

}
