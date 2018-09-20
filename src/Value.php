<?php

namespace Fruit\CompileKit;

/**
 * Value renders a PHP value to piece of PHP expression.
 */
class Value implements Renderable
{
    private $ret = '';

    /**
     * Helper to create Renderable from raw php code.
     */
    public static function as(string $code): Renderable
    {
        return (new self)->raw($code);
    }

    /**
     * Helper to convert some values to Renderable.
     */
    public static function of($value): Renderable
    {
        if ($value instanceof Renderable) {
            return $value;
        }

        return (new self)->bind($value);
    }

    /**
     * Helper to convert an expression to statement by appending a colon.
     */
    public static function stmt(Renderable $r): Renderable
    {
        return new class($r) implements Renderable {
            private $r;

            public function __construct(Renderable $r)
            {
                $this->r = $r;
            }

            public function render(bool $p = false, int $i = 0): string
            {
                return $this->r->render($p, $i) . ';';
            }
        };
    }

    /**
     * Set raw php code.
     *
     * @param $code string raw PHP code.
     */
    public function raw(string $code): Value
    {
        $this->ret = $code;
        return $this;
    }

    /**
     * Set php code by converting val with var_export().
     *
     * @param $val mixed value to be var_export'ed.
     */
    public function bind($val): Value
    {
        $this->ret = var_export($val, true);
        return $this;
    }

    /**
     * Using Renderable to generate php code.
     *
     * @param $r Renderable
     */
    public function set(Renderable $r): Value
    {
        $this->ret = $r;
        return $this;
    }

    /**
     * @see Renderable
     */
    public function render(bool $pretty = false, int $indent = 0): string
    {
        if ($this->ret instanceof Renderable) {
            return $this->ret->render($pretty, $indent);
        }

        $str = '';
        if ($pretty) {
            if ($indent < 0) {
                $indent = 0;
            }
            $str = str_repeat(' ', $indent * 4);
        }

        return $str . $this->ret;
    }
}
