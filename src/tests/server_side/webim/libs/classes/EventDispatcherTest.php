<?php

require_once dirname(__FILE__) . '/../../../../../mibew/libs/classes/event_dispatcher.php';
require_once dirname(__FILE__) . '/../../../../../mibew/libs/classes/plugin.php';
require_once dirname(__FILE__) . '/../../plugins/phpunit_autotest_plugin_manager/phpunit_autotest_plugin_manager_plugin.php';

/**
 * Test class for EventDispatcher.
 * Generated by PHPUnit on 2012-07-17 at 16:09:00.
 */
class EventDispatcherTest extends PHPUnit_Framework_TestCase {

	protected static $plugin = null;

	public static function setUpBeforeClass() {
		self::$plugin = new PhpunitAutotestPluginManagerPlugin();
	}

	public static function tearDownAfterClass() {
		self::$plugin = null;
	}

	public function testGetInstance() {
		$dispatcher = EventDispatcher::getInstance();
		$another_dispatcher = EventDispatcher::getInstance();
		$this->assertSame($dispatcher, $another_dispatcher);
		unset($another_dispatcher);
		return $dispatcher;
	}

	/**
	 * @depends testGetInstance
	 */
	public function testAttachListener($dispatcher) {
		// Try to Attach wrong method as listener to event
		// Following code wait for trigger user error, which converts by PHPUnit to an
		// Exception
		try{
			$dispatcher->attachListener(
				'some_test_event',
				self::$plugin,
				'wrongEventListener'
			);
			$this->fail("Error expected!");
		} catch(Exception $e) {}

		// Try to attach listener to event
		$this->assertTrue(
			$dispatcher->attachListener(
				'some_test_event',
				self::$plugin,
				'testEventListener'
			)
		);

		// Try to attach listener to event
		$this->assertTrue(
			$dispatcher->attachListener(
				'some_another_test_event',
				self::$plugin,
				'testEventListener'
			)
		);

		return $dispatcher;
	}

	/**
	 * @depends testAttachListener
	 */
	public function testDetachListener($dispatcher) {
		// Try to detach listner that was not attached to registerd event
		$this->assertFalse(
			$dispatcher->detachListener(
				'some_test_event',
				self::$plugin,
				'wrongEventListener'
			)
		);

		// Try to detach listener that was attached to registered
		$this->assertTrue(
			$dispatcher->detachListener(
				'some_test_event',
				self::$plugin,
				'testEventListener'
			)
		);
		return $dispatcher;
	}

	/**
	 * @depends testDetachListener
	 */
	public function testTriggerEvent($dispatcher) {
		// Try to trigger registered event
		$test_array = array();
		$dispatcher->triggerEvent('some_another_test_event', $test_array);
		$this->assertEquals('some_test_value', $test_array['test']);
	}

}

?>