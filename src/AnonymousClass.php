<?php

namespace Fruit\CompileKit;

/**
 * AnonymousClass represents PHP 7.0+ anonymous class syntax.
 */
class AnonymousClass implements Renderable
{
    private $props = [];
    private $methods = [];
    private $consts = [];
    private $parent = '';
    private $faces = [];
    private $traits = [];
    private $args = [];

    /**
     * Create a new property.
     *
     * The type hint of returned Argument is ignored.
     *
     *     $c->has('prop1')->bindDefault(1); // public $prop1 = 1;
     *     // following code generates private static $prop1 = 1;
     *     $c->has('prop1', 'private', true)->bindDefault(1);
     *
     * @param $name string property name.
     * @param $visibility string property visibility, default to public.
     * @param $static bool true if this is a static property, default to false.
     */
    public function has(
        string $name,
        string $visibility = 'public',
        bool $static = false
    ): Argument {
        $ret = new Argument($name);
        array_push($this->props, [$ret, $visibility, $static]);

        return $ret;
    }

    /**
     * This is helper for AnonymousClass::has.
     *
     * It accepts raw PHP code for property default value, as you cannot use complex
     * value at property definition.
     *
     * @param $name string property name.
     * @param $visibility string property visibility, default to public.
     * @param $static bool true if this is a static property, default to false.
     * @param $default string php code to initialize property at difinition. (optional)
     */
    public function prop(
        string $name,
        string $visibility = 'public',
        bool $static = false,
        string $default = ''
    ): AnonymousClass {
        $this->has($name, $visibility, $static)->rawDefault($default);
        return $this;
    }

    /**
     * Add methods to this class.
     */
    public function method(UserMethod ...$methods): AnonymousClass
    {
        foreach ($methods as $m) {
            array_push($this->methods, $m);
        }

        return $this;
    }

    /**
     * Add a method to this class.
     */
    public function can(
        string $name,
        string $visibility = 'public',
        bool $static = false
    ): UserMethod {
        $ret = new UserMethod($name, $visibility, $static);
        array_push($this->methods, $ret);
        return $ret;
    }

    /**
     * Add a class constant to this class.
     *
     * Since you cannot use complex value as constants, it supports only raw php
     * code to initialize constant.
     */
    public function const(string $name, string $val): AnonymousClass
    {
        array_push($this->consts, $name . ' = ' . $val);

        return $this;
    }

    /**
     * Set the parent class of this class.
     *
     * You MUST take care of namespace.
     */
    public function extends(string $cls): AnonymousClass
    {
        $this->parent = $cls;
        return $this;
    }

    /**
     * Declares the class to implement specified interface.
     *
     * You MUST take care of namespace.
     */
    public function implements(string ...$faces): AnonymousClass
    {
        foreach ($faces as $f) {
            array_push($this->faces, $f);
        }

        return $this;
    }

    /**
     * Using traits in this class.
     *
     * You MUST take care of namespace.
     */
    public function use(string ...$traits): AnonymousClass
    {
        foreach ($traits as $t) {
            array_push($this->traits, $t);
        }

        return $this;
    }

    /**
     * Passing some values to constructor.
     *
     * @param $args string[] raw php codes.
     */
    public function rawArgs(string ...$args): AnonymousClass
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
    public function bindArgs(...$args): AnonymousClass
    {
        foreach ($args as $a) {
            array_push($this->args, (new Value)->bind($a));
        }

        return $this;
    }

    /**
     * Passing some Renderable to constructor.
     */
    public function setArgs(Renderable ...$args): AnonymousClass
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

        return 'new class'
            . $this->renderTyping($pretty, $indent)
            . $this->renderBody($pretty, $indent);
    }

    private function renderTyping(bool $pretty, int $indent): string
    {
        $str = '';
        $lf = '';
        if ($pretty) {
            $str = str_repeat(' ', ($indent+1) * 4);
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
        array_push($arr, ')');

        // extends
        if ($this->parent !== '') {
            $arr[count($arr)-1] .= ' extends ' . $this->parent;
        }

        if (count($this->faces) > 0) {
            $arr[count($arr)-1] .= ' implements' . ($pretty?'':' ');
            array_push($arr, $str . implode(',' . $lf . $str, $this->faces));
        }

        return implode($lf, $arr) . $lf;
    }

    private function renderBody(bool $pretty, int $indent): string
    {
        $str = '';
        $next = '';
        $lf = '';
        if ($pretty) {
            $str = str_repeat(' ', $indent * 4);
            $next = str_repeat(' ', ($indent+1) * 4);
            $lf = "\n";
        }
        $arr = [$str . '{'];

        // render traits
        if (count($this->traits) > 0) {
            foreach ($this->traits as $t) {
                array_push($arr, $next . 'use ' . $t . ';');
            }
            if ($pretty) {
                // add empty line
                array_push($arr, '');
            }
        }

        // render constants
        if (count($this->consts) > 0) {
            foreach ($this->consts as $c) {
                array_push($arr, $next . 'const ' . $c . ';');
            }
            if ($pretty) {
                // add empty line
                array_push($arr, '');
            }
        }

        // render properties
        if (count($this->props) > 0) {
            foreach ($this->props as $p) {
                $prop = $p[1] . ' ';
                if ($p[2]) {
                    $prop .= 'static ';
                }
                $prop .= $p[0]->type('')->render();

                array_push($arr, $next . $prop . ';');
            }
            if ($pretty) {
                // add empty line
                array_push($arr, '');
            }
        }

        // render methods
        if (count($this->methods) > 0) {
            $nextLV = 0;
            if ($pretty) {
                $nextLV = $indent+1;
            }
            foreach ($this->methods as $m) {
                array_push($arr, $m->render($pretty, $nextLV));
                // add empty line
                array_push($arr, '');
            }
            array_pop($arr);
        }

        return implode($lf, $arr) . $lf . $str . '}';
    }
}
