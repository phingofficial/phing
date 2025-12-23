<?php

declare(strict_types=1);

namespace Phing\Type;

class Payload
{
    private string $text = '';
    private bool $trim = true;

    public function addText(string $text): void
    {
        $this->text = $text;
    }

    public function setTrim(bool $trim): void
    {
        $this->trim = $trim;
    }

    public function getText(): string
    {
        return $this->trim ? \trim($this->text) : $this->text;
    }
}
