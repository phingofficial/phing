<?php

    /**
     * The Hello World class!
     *
     * @author Michiel Rook
     */
    class HelloWorld
    {
        public function foo($silent = true)
        {
            if ($silent) {
                return;
            }

            return 'foo';
        }

        public function sayHello()
        {
            return 'Hello World!';
        }
    }
