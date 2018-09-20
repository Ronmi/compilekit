<?php

namespace Fruit\CompileKit;

class UserFunction
{
    private $name;
    private $args = [];
    private $returnType = '';
    private $body = [];

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }

    public function accept(string $name): Argument
    {
        $ret = new Argument($name);
        array_push($this->args, $ret);

        return $ret;
    }

    public function arg(string $name, string $type = '', string $default = ''): UserFunction
    {
        $this->accept($name)
            ->type($type)
            ->val($default);

        return $this;
    }

    public function argve(string $name, $default, string $type = ''): UserFunction
    {
        $this->accept($name)
            ->type($type)
            ->var($default);

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

    public function render(bool $pretty = false, int $indent = 0): string
    {
        $ret = $this->returnType;
        if ($this->returnType !== '') {
            $ret = ': ' . $ret;
        }

        if ($indent < 0) {
            $indent = 0;
        }

        $args = array_map(function ($a) {
            return $a->render();
        }, $this->args);

        return sprintf(
            '%sfunction %s(%s)%s%s',
            str_repeat(' ', $indent * 4),
            $this->name,
            implode(', ', $args),
            $ret,
            $this->renderBody($pretty, $indent)
        );
    }

    private function renderBody(bool $pretty, int $lv): string
    {
        if (!$pretty) {
            return ' {' . implode('', $this->body) . '}';
        }

        $indent = str_repeat(' ', $lv * 4);

        $ret = sprintf(
            '
%s{
%s    %s
%s}',
            $indent,
            $indent,
            implode("\n" . $indent . '    ', $this->body),
            $indent
        );

        $arr = explode("\n", $ret);
        foreach ($arr as $k => $v) {
            if (trim($v) === '') {
                $arr[$k] = '';
            }
        }
        return implode("\n", $arr);
    }
}
