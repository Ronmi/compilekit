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

        if ($value instanceof Compilable) {
            return (new self)->by($value);
        }

        return (new self)->bind($value);
    }

    /**
     * Helper to convert an expression to statement by appending a colon.
     */
    public static function stmt(Renderable ...$r): Renderable
    {
        return new class($r) implements Renderable {
            private $r;

            public function __construct(array $r)
            {
                $this->r = $r;
            }

            public function render(bool $p = false, int $i = 0): string
            {
                if ($i < 0) {
                    $i = 0;
                }
                $str = '';
                if ($p) {
                    $str = str_repeat(' ', $i * 4);
                }

                $arr = array_map(function (Renderable $r) use ($p, $i) {
                    $ret = $r->render($p, $i);
                    if ($p and $i > 0) {
                        $ret = substr($ret, $i * 4);
                    }
                    return $ret;
                }, $this->r);

                return $str . implode(' ', $arr) . ';';
            }
        };
    }

    /**
     * Helper to prevent pretty formater.
     */
    public static function ugly(Renderable $r): Renderable
    {
        return new class($r) implements Renderable {
            private $r;

            public function __construct(Renderable $r)
            {
                $this->r = $r;
            }

            public function render(bool $p = false, int $i = 0): string
            {
                return $this->r->render();
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
     * Using Compilable to generate php code.
     *
     * Value uses "late-compile" working flow: $c->compile() will be called in
     * Value::render, not here.
     *
     *     $b = (new Block)->line('$a = 1;');
     *     $v = (new Value)->by($b);
     *     $b->line('$b = $a;');
     *     $v->render(); // $a = 1;$b = $a;
     *
     * @param $c Compilable
     */
    public function by(Compilable $c): Value
    {
        $this->ret = $c;
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

        if ($this->ret instanceof Compilable) {
            return $this->ret->compile()->render($pretty, $indent);
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
