<?php

namespace Fruit\CompileKit;

/**
 * Value renders a PHP value to piece of PHP expression.
 */
class Value implements Renderable
{
    private $ret = '';

    public static function __set_state(array $data)
    {
        $ret = new self();
        foreach ($data as $k => $v) {
            $ret->$k = $v;
        }
        return $ret;
    }

    /**
     * Helper to create Renderable from raw php code.
     *
     * @param $code string of raw php code.
     * @return Renderable instance.
     */
    public static function as(string $code): Renderable
    {
        return (new self)->raw($code);
    }

    /**
     * Helper to convert some values to Renderable.
     *
     * It converts $value to Renderable if needed:
     *
     * 1. $value is Renderable: not convertion, just return it.
     * 2. $value is Compilable: same as Value::by($value).
     * 3. anyother: same as Value::bind($value).
     *
     * @param $value mixed value.
     * @return Renderable instance.
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
     * Helper to create assignment statement.
     *
     *     // result: '$a = $b;'
     *     Value::assign(Value::as('$a'), Value::as('$b'))->render();
     *
     * @deprecated use Block::assign instead.
     * @param $to Renderable at left side of assignment.
     * @param $from Renderable at right side of assignment.
     * @return Renderable instance.
     */
    public static function assign(Renderable $to, Renderable $from): Renderable
    {
        return (new Block)->assign($to, $from);
    }

    /**
     * Helper to convert an expression to statement by appending a colon.
     *
     * @deprecated use Block::stmt instead.
     * @param $r Renderable to render.
     * @return Renderable instance.
     */
    public static function stmt(Renderable ...$r): Renderable
    {
        return (new Block)->stmt(...$r);
    }

    /**
     * Helper to prevent pretty formatting.
     *
     * @param $r Renderable instance.
     * @return Renderable instance.
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
                return $this->r->render(false, $i);
            }
        };
    }

    /**
     * Set raw php code.
     *
     * @param $code string raw PHP code.
     * @return self
     */
    public function raw(string $code): self
    {
        $this->ret = $code;
        return $this;
    }

    /**
     * Set php code by converting val with var_export().
     *
     * @param $val mixed value to be var_export'ed.
     * @return self
     */
    public function bind($val): self
    {
        $this->ret = var_export($val, true);
        return $this;
    }

    /**
     * Using Renderable to generate php code.
     *
     * @param $r Renderable
     * @return self
     */
    public function set(Renderable $r): self
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
     * @return self
     */
    public function by(Compilable $c): self
    {
        $this->ret = $c;
        return $this;
    }

    /**
     * @see Renderable
     * @return string of generated php code.
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
