<?php

namespace Fruit\CompileKit;

/**
 * UserFunction is a helper to define your own function.
 *
 * It supports both named and anonymouse functions.
 *
 *     $f = new UserFunction('f');
 *     $f->accept('a')->type('string');
 *     $f->accept('b')->type('int')->var(0);
 *     $f
 *         ->return('string')
 *         ->line('$ret = $a . $b;')
 *         ->line('return $ret;');
 *     $f->render();
 *     // function f(string $a, int $b = 0) {$ret = $a . $b;return $ret;}
 *     $f->render(true);
 *     // function f(string $a, int $b = 0)
 *     // {
 *     //     $ret = $a . $b;
 *     //     return $ret;
 *     // }
 */
class UserFunction implements Renderable
{
    private $name;
    private $args = [];
    private $returnType = '';
    private $body;
    private $use = [];

    protected function restore(array $data)
    {
        $this->args = $data['args'];
        $this->returnType = $data['returnType'];
        $this->body = $data['body'];
        $this->use = $data['use'];
    }

    public static function __set_state(array $data)
    {
        $ret = new self();
        foreach ($data as $k => $v) {
            $ret->$k = $v;
        }
        return $ret;
    }

    /**
     * The constructor.
     *
     * Pass the name of the function here. Use empty string for anonymous function.
     * Name is NOT validated, might be supported in later version.
     *
     * @param $name string function name, use empty string for anonymous function.
     */
    public function __construct(string $name = '')
    {
        $this->name = $name;
        $this->body = new Block;
    }

    /**
     * Set inherited parameters.
     *
     * Only anonymous function will use this. Named function will ignore it.
     *
     * @param $vars string variable names to inherit
     * @return self
     */
    public function use(string ...$vars): self
    {
        foreach ($vars as $v) {
            if ($v[0] !== '$') {
                $v = '$' . $v;
            }
            array_push($this->use, $v);
        }

        return $this;
    }

    /**
     * Add an argument to the function.
     *
     *     $f->accept('a')->type('string');      // function (string $a)
     *     $f->accept('b')->type('int')->var(0); // function (string $a, int $b = 0)
     *
     * @see UserFunction::arg
     * @param $name string argument name
     * @return Argument instance.
     */
    public function accept(string $name): Argument
    {
        $ret = new Argument($name);
        array_push($this->args, $ret);

        return $ret;
    }

    /**
     * Setting function arguments.
     *
     * This is helper method for UserFunction::accept.
     *
     *     // generates function (string $a, int $b = 0)
     *     $userFunc->rawArg('$a', 'string')->rawArg('b', 'int', '0');
     *
     * You have to pass PHP SOURCE CODE to the $default. You can do it easily with
     * `var_export()` for primitive values.
     *
     *     $userFunc->rawArg('a', 'int', var_export(1, true));
     *
     * @see UserFunction::accept
     * @see UserFunction::bindArg
     * @param $name string name of the argument.
     * @param $type string type hint of the argument. (optional)
     * @param $default string PHP SOURCE CODE of default value of the argument. (optional)
     * @return self
     */
    public function rawArg(string $name, string $type = '', string $default = ''): self
    {
        $this->accept($name)
            ->type($type)
            ->rawDefault($default);

        return $this;
    }

    /**
     * Setting function arguments.
     *
     * This is helper method for UserFunction::accept.
     *
     *     // generates function (int $a = 1)
     *     $userFunc->bindArg('a', 1, 'int')->bindArg('b', true);
     *
     * WARNING: Order of arguments between rawArg and bindArg is different.
     *
     * @param $name string name of the argument.
     * @param $default string default value of the argument. MUST COMPITABLE WITH var_export().
     * @param $type string type hint of the argument. (optional)
     * @return self
     */
    public function bindArg(string $name, $default, string $type = ''): self
    {
        $this->accept($name)
            ->type($type)
            ->bindDefault($default);

        return $this;
    }

    /**
     * Define return type
     *
     * @param $type string return type
     * @return self
     */
    public function return(string $type): self
    {
        $this->returnType = $type;

        return $this;
    }

    /**
     * Append a line of php code to function body.
     *
     * @param $line string php code
     * @return self
     */
    public function line(string $line): self
    {
        $this->body->line($line);

        return $this;
    }

    /**
     * Append multiple lines of php code to function body.
     *
     * @param $block string array of php codes
     * @return self
     */
    public function block(array $block): self
    {
        $this->body->line(...$block);

        return $this;
    }

    /**
     * Append a block of php code to function body.
     *
     * @param $block string array of php codes
     * @return self
     */
    public function append(Block $block): self
    {
        $this->body->append($block);

        return $this;
    }

    /**
     * @see Renderable
     * @param $pretty bool true to generate multi-line code, default to false
     * @param $indent int indent level, used if $pretty is true
     * @return string of generated php code.
     */
    public function render(bool $pretty = false, int $indent = 0): string
    {
        $lf = '';
        $str = '';
        if ($pretty) {
            if ($indent < 0) {
                $indent = 0;
            }
            $lf = "\n";
            $str = str_repeat(' ', $indent * 4);
        }
        $ret = $this->returnType;
        if ($this->returnType !== '') {
            $ret = ': ' . $ret;
        }

        if ($indent < 0) {
            $indent = 0;
        }

        $args = array_map(function ($a) use ($pretty, $indent) {
            return $a->render($pretty, $indent+1);
        }, $this->args);

        if ($this->name === '' and count($this->use) > 0) {
            $str2 = '';
            if ($pretty) {
                $str2 = '    ';
            }
            $ret = ' use (' . $lf . $str . $str2
                . implode(',' . $lf . $str . $str2, $this->use) . $lf
                . $str . ')' . $ret;
        }

        $lfArg = '';
        $strArg = '';
        $neck = $lf;
        if (count($args) > 0) {
            $lfArg = $lf;
            $strArg = $str;
        }
        if (!$pretty or count($args) + count($this->use) > 0) {
            $neck = ' ';
        }

        return $str . 'function ' . $this->name . '(' . $lfArg
            . implode(',' . $lfArg, $args) . $lfArg
            . $strArg . ')' . $ret . $neck
            . $this->renderBody($pretty, $indent);
    }

    private function renderBody(bool $pretty, int $indent): string
    {
        if (!$pretty) {
            return '{' . $this->body->render() . '}';
        }

        $str = str_repeat(' ', $indent * 4);
        $ret = '';
        if (count($this->args) === 0) {
            $ret = $str;
        }

        return $ret . "{\n"
            . $this->body->render($pretty, $indent+1)
            . "\n" . $str . '}';
    }
}
