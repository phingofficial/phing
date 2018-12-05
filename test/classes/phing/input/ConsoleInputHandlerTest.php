<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;

class ConsoleInputHandlerTest extends TestCase
{
    public function testDefaultValue() 
    {
        $inputStream = $this->createStream([' ']);
        $output = new NullOutput();
        $request = new InputRequest("Enter a value");
        $request->setDefaultValue('default');
        $handler = new ConsoleInputHandler($inputStream, $output);
        
        $handler->handleInput($request);
        
        self::assertEquals('default', $request->getInput());
    }

    public function testMultipleChoiceQuestion() 
    {
        $inputStream = $this->createStream(['choice1']);
        $output = new NullOutput();
        $request = new MultipleChoiceInputRequest("Enter a choice", ['choice1', 'choice2']);    
        $handler = new ConsoleInputHandler($inputStream, $output);
        
        $handler->handleInput($request);
        
        self::assertEquals('choice1', $request->getInput());
    }

    public function testYesNoQuestion() 
    {
        $inputStream = $this->createStream(['no']);
        $output = new NullOutput();
        $request = new YesNoInputRequest("Enter a choice", ['yes', 'no']);    
        $handler = new ConsoleInputHandler($inputStream, $output);
        
        $handler->handleInput($request);
        
        self::assertFalse($request->getInput());
    }

    private function createStream(array $inputs)
    {
        $stream = fopen('php://memory', 'r+', false);
        fwrite($stream, implode(PHP_EOL, $inputs));
        rewind($stream);
        return $stream;
    }
}
