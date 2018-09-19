<?php

namespace Fruit\CompileKit;

class UserFunction
{
    private $name;
    private $args = [];
    private $returnType = '';
    private $body = [];
    private $pretty;

    public function __construct(string $name = '', bool $pretty = false)
    {
        $this->name = $name;
        $this->pretty = $pretty;
    }

    public function arg(string $name, string $type = '', string $default = ''): UserFunction
    {
        if ($name[0] !== '$') {
            $name = '$' . $name;
        }

        if ($type !== '') {
            $type .= ' ';
        }

        if ($default !== '') {
            $default = ' = ' . $default;
        }

        array_push($this->args, $type . $name . $default);

        return $this;
    }

    public function return(string $type): UserFunction
    {
        $this->returnType = $type;

        return $this;
    }

    public function line(string $line): UserFunction
    {
        array_push($this->body, $line);

        return $this;
    }

    public function block(array $block): UserFunction
    {
        foreach ($block as $line) {
            array_push($this->body, $line);
        }

        return $this;
    }

    public function __toString(): string
    {
        $ret = $this->returnType;
        if ($this->returnType !== '') {
            $ret = ': ' . $ret;
        }

        return sprintf(
            'function %s(%s)%s%s',
            $this->name,
            implode(', ', $this->args),
            $ret,
            $this->renderBody()
        );
    }

    private function renderBody(): string
    {
        if (!$this->pretty) {
            return ' {' . implode('', $this->body) . '}';
        }

        $ret = "\n{\n    ";
        $ret .= implode("\n    ", $this->body);
        $ret .= "\n}";

        return $ret;
    }
}
