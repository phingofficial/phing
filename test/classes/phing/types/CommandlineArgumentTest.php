<?php
class CommandlineArgumentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the one 'getter' method of the CommandlineArgument class
     *
     * @return void
     */
    public function testGetParts()
    {
        $command = "usblamp -s -r 5 red green blue off";
        $exploded = explode(" ", "-s -r 5 red green blue off");
        $commandline = new Commandline($command);
        $arguments = ($commandline->arguments);
        foreach ($arguments as $counter => $argument) {
            $parts = $argument->getParts();
            $this->assertEquals($exploded[$counter], $parts[0]);
            $this->assertEquals(false, $argument->escape);
        }
    }
}
