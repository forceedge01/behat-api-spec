<?php

namespace Genesis\BehatApiSpec\Service;

use Exception;

class StringBuilder
{
    private $string = '';

    private $tabLevel = 1;

    public static function newInstance($tabLevel = 1): self
    {
        $builder = new static();
        $builder->setTabLevel($tabLevel);

        return $builder;
    }

    public function setTabLevel(int $tabLevel): self
    {
        $this->tabLevel = $tabLevel;

        return $this;
    }

    public function incrementTabLevel(): self
    {
        $this->tabLevel += 1;

        return $this;
    }

    public function decrementTabLevel(): self
    {
        if ($this->tabLevel === 0) {
            throw new Exception('Tab level already at 0, cannot decrement.');
        }

        $this->tabLevel -= 1;

        return $this;
    }

    public function addLine(string $line): self
    {
        $this->string .= $this->tab($this->tabLevel) . $line . PHP_EOL;

        return $this;
    }

    public function newLine(): self
    {
        $this->string .= PHP_EOL;

        return $this;
    }

    public function getString(): string
    {
        return $this->string;
    }

    private function tab(int $count): string
    {
        return str_repeat(' ', $count * 4);
    }
}
