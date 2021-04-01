<?php

declare(strict_types=1);

namespace Phing\Test\Task\System;

use Phing\Test\Support\BuildFileTest;

/**
 * @author Mahmoud Al-Husseiny <mahmoud@alhusseiny.io>
 */
class PhpEvalTaskTest extends BuildFileTest
{
    public static function recursiveProcess(array $arr): string
    {
        // ensure n-d array (n > 1)
        $isMultiDimArray = false;
        foreach ($arr as $value) {
            if (is_array($value) && !empty($value)) {
                $isMultiDimArray = true;
                break;
            }
        }
        static::assertTrue(
            $isMultiDimArray,
            'You must provide a multidimensional array for this test'
        );

        $arraySum = '';
        array_walk_recursive($arr, function ($item) use (&$arraySum) {
            $arraySum .= $item;
        });

        return $arraySum;
    }

    protected function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/system/PhpEvalTest.xml');
    }

    public function testZeroParams()
    {
        $expected = get_include_path();

        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('result', $expected);
    }

    public function testOneScalarParam()
    {
        $expected = trim('   test   ');

        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('result', $expected);
    }

    public function testMultiScalarParams()
    {
        $expected = trim('##**test**##', '#');

        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('result', $expected);
    }

    public function testArrayParam()
    {
        $expected = implode(['Phing', ' ', '3']);

        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('result', $expected);
    }

    public function testMixedParams()
    {
        $expected = implode(' ', ['Phing', '3']);

        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('result', $expected);
    }

    public function testStaticMethodWithMultiDimArrayParam()
    {
        $expected = static::recursiveProcess(['a', ['b', 'c'], 'd']);

        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('result', $expected);
    }
}
