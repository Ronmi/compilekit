<?php

namespace Fruit\CompileKit;

class Argument
{
    private $name;
    private $typeHint = '';
    private $defaultValue;

    public function __construct(string $name)
    {
        if ($name[0] !== '$') {
            $name = '$' . $name;
        }

        $this->name = $name;
        $this->defaultValue = new Value;
    }

    public function type(string $type): Argument
    {
        $this->typeHint = $type;
        return $this;
    }

    public function defaults(): Value
    {
        return $this->defaultValue;
    }

    public function rawDefault(string $val): Argument
    {
        $this->defaults()->raw($val);
        return $this;
    }

    public function bindDefault($val): Argument
    {
        $this->defaults()->bind($val);
        return $this;
    }

    public function setDefault($val): Argument
    {
        $this->defaults()->set($val);
        return $this;
    }

    public function render(bool $pretty = false, int $indent = 0): string
    {
        $padding = '';
        if ($pretty and $indent > 0) {
            $padding = str_repeat(' ', $indent * 4);
        }

        $t = $this->typeHint;
        if ($t !== '') {
            $t .= ' ';
        }

        $d = $this->defaultValue->render();
        if ($d !== '') {
            $d = ' = ' . $d;
        }

        return $padding . $t . $this->name . $d;
    }
}
