<?php
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'phing/util/properties/PropertySetImpl.php';

class PropertySetImplTest extends PHPUnit_Framework_TestCase {
	
	public function testFindPrefix() {
		$s = new PropertySetImpl();
		$s['foo.one'] = 1;
		$s['foo.two'] = 2;
		$s['bar'] = 42;
		
		$fooValues = $s->prefix('foo');
		$this->assertTrue(isset($fooValues['one']));
		$this->assertFalse(isset($fooValues['foo.one']));
		$this->assertFalse(isset($fooValues['bar']));
		$this->assertEquals(1, $fooValues['one']);
	}
}
