<?php

class provider_failTest extends \PHPUnit\Framework\TestCase {
 public function test_simplefail() {
  self::assertFalse(true);
 }
 /**
  * @dataProvider provider
  */
 public function test_provider($v1,$v2) {
  self::assertEquals($v1,$v2);
 }

 public function provider() {
  return [
   [true,true]
  ];
 }
}
