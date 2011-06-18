<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'phing/util/properties/PropertySetImpl.php';
require_once 'phing/util/properties/PropertyExpansionWrapper.php';

class PropertyExpansionTest extends PHPUnit_Framework_TestCase {
	
	public function testFindPrefix() {
		$s = new PropertyExpansionWrapper(new PropertySetImpl());
		
		$s['foo.one'] = 1;
		$s['foo.fortytwo'] = '${bar}';
		$s['bar'] = 42;

		$this->assertEquals(42, $s['foo.fortytwo']);

		// Test that (transparent) expansion still works
		// after re-basing property set.
		
		$foo = $s->prefix('foo');
		
		$this->assertTrue(isset($foo['one']));
		$this->assertFalse(isset($foo['foo.one']));
		$this->assertFalse(isset($foo['bar']));
		$this->assertEquals(42, $foo['fortytwo']);
		
		
	}
}
