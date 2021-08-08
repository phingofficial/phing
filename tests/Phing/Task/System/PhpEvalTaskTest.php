<?php

declare(strict_types=1);

namespace Phing\Test\Task\System;

use Phing\Test\Support\BuildFileTest;

/**
 * @author Mahmoud Al-Husseiny <mahmoud@alhusseiny.io>
 *
 * @internal
 */
class PhpEvalTaskTest extends BuildFileTest
{
    protected function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/system/PhpEvalTest.xml');
    }

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
        array_walk_recursive($arr, static function ($item) use (&$arraySum) {
            $arraySum .= $item;
        });

        return $arraySum;
    }

    public function testZeroParams(): void
    {
        $expected = get_include_path();

        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('result', $expected);
    }

    public function testOneScalarParam(): void
    {
        $expected = trim('   test   ');

        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('result', $expected);
    }

    public function testMultiScalarParams(): void
    {
        $expected = trim('##**test**##', '#');

        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('result', $expected);
    }

    public function testArrayParam(): void
    {
        $expected = implode(['Phing', ' ', '3']);

        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('result', $expected);
    }

    public function testMixedParams(): void
    {
        $expected = implode(' ', ['Phing', '3']);

        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('result', $expected);
    }

    public function testStaticMethodWithMultiDimArrayParam(): void
    {
        $expected = static::recursiveProcess(['a', ['b', 'c'], 'd']);

        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('result', $expected);
    }
}
