<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class NoInteractionInputHandlerTest extends TestCase
{
    public function testDefaultValue()
    {
        $request = new InputRequest('Enter a value');
        $request->setDefaultValue('default');
        $handler = new NoInteractionInputHandler();

        $handler->handleInput($request);

        self::assertEquals('default', $request->getInput());
    }

    public function testMultipleChoiceQuestion()
    {
        $request = new MultipleChoiceInputRequest('Enter a choice', ['choice1', 'choice2']);
        $handler = new NoInteractionInputHandler();

        $handler->handleInput($request);

        self::assertNull($request->getInput());
    }

    public function testYesNoQuestion()
    {
        $request = new YesNoInputRequest("Enter a choice", ['yes', 'no']);
        $handler = new NoInteractionInputHandler();

        $handler->handleInput($request);

        self::assertFalse($request->getInput());
    }
}
