<?php

class Proxy
{
    private $text = '';

    public function addText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }
}

class RuntimeConfigurableTest extends PHPUnit_Framework_TestCase
{
    public function testLiteral0ShouldBeKept()
    {
        $project = new Project();
        $proxy = new Proxy();
        $runtimeConfigurable = new RuntimeConfigurable($proxy, 'proxy');
        $runtimeConfigurable->addText('0');
        $runtimeConfigurable->maybeConfigure($project);
        $this->assertSame('0', $proxy->getText());
    }
}
