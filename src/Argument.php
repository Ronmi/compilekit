<?php

namespace Fruit\CompileKit;

class Argument
{
    private $name;
    private $typeHint = '';
    private $defaultValue = '';

    public function __construct(string $name)
    {
        if ($name[0] !== '$') {
            $name = '$' . $name;
        }

        $this->name = $name;
    }

    public function type(string $type): Argument
    {
        $this->typeHint = $type;
        return $this;
    }

    public function val(string $val): Argument
    {
        $this->defaultValue = $val;
        return $this;
    }

    public function var($val): Argument
    {
        return $this->val(var_export($val, true));
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

        $d = $this->defaultValue;
        if ($d !== '') {
            $d = ' = ' . $d;
        }

        return $padding . $t . $this->name . $d;
    }
}
