<?php

namespace Fruit\CompileKit;

/**
 * UserClass denotes an user-specific class declaration.
 */
class UserClass implements Renderable
{
    private $name;
    protected $parent = '';
    protected $faces = [];
    protected $props = [];
    protected $methods = [];
    protected $consts = [];
    protected $traits = [];

    public static function __set_state(array $data)
    {
        $ret = new self('');
        foreach ($data as $k => $v) {
            $ret->$k = $v;
        }
        return $ret;
    }

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * Create a new property.
     *
     * The type hint of returned Argument is ignored.
     *
     *     $c->has('prop1')->bindDefault(1); // public $prop1 = 1;
     *
     * @param $name string property name.
     * @param $visibility string property visibility, default to public.
     */
    public function has(string $name, string $visibility = 'public'): Argument
    {
        return $this->addProp($name, $visibility, false);
    }

    /**
     * Create a new static property.
     *
     * The type hint of returned Argument is ignored.
     *
     *     $c->has('prop1')->bindDefault(1); // public $prop1 = 1;
     *     // following code generates private static $prop1 = 1;
     *     $c->hasStatic('prop1', 'private')->bindDefault(1);
     *
     * @param $name string property name.
     * @param $visibility string property visibility, default to public.
     */
    public function hasStatic(
        string $name,
        string $visibility = 'public'
    ): Argument {
        return $this->addProp($name, $visibility, true);
    }

    private function addProp(
        string $name,
        string $visibility,
        bool $static
    ): Argument {
        $ret = new Argument($name);
        array_push($this->props, [$ret, $visibility, $static]);

        return $ret;
    }

    /**
     * This is helper for UserClass::has and UserClass::hasStatic.
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
    ): UserClass {
        $this->addProp($name, $visibility, $static)->rawDefault($default);
        return $this;
    }

    /**
     * Add methods to this class.
     */
    public function method(UserMethod ...$methods): UserClass
    {
        foreach ($methods as $m) {
            array_push($this->methods, $m);
        }

        return $this;
    }

    /**
     * Add a method to this class.
     */
    public function can(string $name, string $visibility = 'public'): UserMethod
    {
        return $this->addMethod($name, $visibility, false);
    }

    /**
     * Add a static method to this class.
     */
    public function canStatic(string $name, string $visibility = 'public'): UserMethod
    {
        return $this->addMethod($name, $visibility, true);
    }

    private function addMethod(
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
    public function const(string $name, string $val): UserClass
    {
        array_push($this->consts, $name . ' = ' . $val);

        return $this;
    }

    /**
     * Set the parent class of this class.
     *
     * You MUST take care of namespace.
     */
    public function extends(string $cls): UserClass
    {
        $this->parent = $cls;
        return $this;
    }

    /**
     * Declares the class to implement specified interface.
     *
     * You MUST take care of namespace.
     */
    public function implements(string ...$faces): UserClass
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
    public function use(string ...$traits): UserClass
    {
        foreach ($traits as $t) {
            array_push($this->traits, $t);
        }

        return $this;
    }

    /**
     * Since PHP does not support inner class, indent level is forced to be 0.
     *
     * @see Renderable
     */
    public function render(bool $pretty = false, int $indent = 0): string
    {
        return $this->renderTyping($pretty, 0)
            . $this->renderBody($pretty, 0);
    }

    private function renderTyping(bool $pretty, int $indent): string
    {
        $str = '';
        $lf = '';
        if ($pretty) {
            $str = str_repeat(' ', ($indent+1) * 4);
            $lf = "\n";
        }

        $arr = ['class ' . $this->name];

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

    protected function renderBody(bool $pretty, int $indent): string
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
