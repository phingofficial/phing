<?php
 
class DbmsSyntaxPgSQL extends DbmsSyntax 
{
    public function generateTimestamp()
    {
        return "NOW()";
    }
}

