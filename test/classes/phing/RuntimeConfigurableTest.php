<?php

class RuntimeConfigurableTest extends \PHPUnit\Framework\TestCase
{
    public function testLiteral0ShouldBeKept()
    {
        $project = new Project();
        $proxy = new Proxy();
        $runtimeConfigurable = new RuntimeConfigurable($proxy, 'proxy');
        $runtimeConfigurable->addText('0');
        $runtimeConfigurable->maybeConfigure($project);
        self::assertSame('0', $proxy->getText());
    }
}
