<?php

namespace Fruit\CompileKit;

class AnonymousClass implements Renderable
{
    private $props = [];
    private $methods = [];
    private $consts = [];
    private $parent = '';
    private $faces = [];
    private $traits = [];
    private $args = [];

    public function has(
        string $name,
        string $visibility = 'public',
        bool $static = false
    ): Argument {
        $ret = new Argument($name);
        array_push($this->props, [$ret, $visibility, $static]);

        return $ret;
    }

    public function prop(
        string $name,
        string $visibility = 'public',
        bool $static = false,
        string $default = ''
    ): AnonymousClass {
        $this->has($name, $visibility, $static)->rawDefault($default);
        return $this;
    }

    public function method(UserMethod ...$methods): AnonymousClass
    {
        foreach ($methods as $m) {
            array_push($this->methods, $m);
        }

        return $this;
    }

    public function can(
        string $name,
        string $visibility = 'public',
        bool $static = false
    ): UserMethod {
        $ret = new UserMethod($name, $visibility, $static);
        array_push($this->methods, $ret);
        return $ret;
    }

    public function const(string $name, string $val): AnonymousClass
    {
        array_push($this->consts, $name . ' = ' . $val);

        return $this;
    }

    public function extends(string $cls): AnonymousClass
    {
        $this->parent = $cls;
        return $this;
    }

    public function implements(string ...$faces): AnonymousClass
    {
        foreach ($faces as $f) {
            array_push($this->faces, $f);
        }

        return $this;
    }

    public function use(string ...$traits): AnonymousClass
    {
        foreach ($traits as $t) {
            array_push($this->traits, $t);
        }

        return $this;
    }

    public function args(string ...$args): AnonymousClass
    {
        foreach ($args as $a) {
            array_push($this->args, $a);
        }

        return $this;
    }

    public function argsve(...$args): AnonymousClass
    {
        foreach ($args as $a) {
            array_push($this->args, var_export($a, true));
        }

        return $this;
    }

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

            array_push($arr, $str . implode(',' . $lf . $str, $this->args));
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
