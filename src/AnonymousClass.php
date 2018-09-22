<?php

namespace Fruit\CompileKit;

/**
 * AnonymousClass represents PHP 7.0+ anonymous class syntax.
 */
class AnonymousClass extends UserClass
{
    private $args = [];

    public static function __set_state(array $data)
    {
        $ret = new self();
        foreach ($data as $k => $v) {
            if ($k === 'name') {
                continue;
            }
            $ret->$k = $v;
        }
        return $ret;
    }

    public function __construct()
    {
        parent::__construct('');
    }

    /**
     * Passing some values to constructor.
     *
     * @param $args string[] raw php codes.
     */
    public function rawArgs(string ...$args): self
    {
        foreach ($args as $a) {
            array_push($this->args, (new Value)->raw($a));
        }

        return $this;
    }

    /**
     * Passing some values to constructor.
     *
     * @param $args mixed[] arguments to pass, will be var_export'ed when rendering.
     */
    public function bindArgs(...$args): self
    {
        foreach ($args as $a) {
            array_push($this->args, (new Value)->bind($a));
        }

        return $this;
    }

    /**
     * Passing some Renderable to constructor.
     */
    public function setArgs(Renderable ...$args): self
    {
        foreach ($args as $a) {
            array_push($this->args, $a);
        }

        return $this;
    }

    /**
     * @see Renderable
     */
    public function render(bool $pretty = false, int $indent = 0): string
    {
        if ($indent < 0) {
            $indent = 0;
        }

        return $this->renderTyping($pretty, $indent)
            . $this->renderBody($pretty, $indent);
    }

    private function renderTyping(bool $pretty, int $indent): string
    {
        $str = '';
        $lf = '';
        $heading = '';
        if ($pretty) {
            $str = str_repeat(' ', ($indent+1) * 4);
            $heading = str_repeat(' ', $indent * 4);
            $lf = "\n";
        }

        // constructor arguments
        $arr = ['('];
        if (count($this->args) > 0) {
            $arr[0] = '(';

            $args = array_map(function ($a) use ($pretty, $indent) {
                return $a->render($pretty, $indent + 1);
            }, $this->args);

            array_push($arr, implode(',' . $lf, $args));
        }
        array_push($arr, $heading . ')');

        // extends
        if ($this->parent !== '') {
            $arr[count($arr)-1] .= ' extends ' . $this->parent;
        }

        if (count($this->faces) > 0) {
            $arr[count($arr)-1] .= ' implements' . ($pretty?'':' ');
            array_push($arr, $str . implode(',' . $lf . $str, $this->faces));
        }

        return $heading . 'new class' . implode($lf, $arr) . $lf;
    }
}
