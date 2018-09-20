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
     * Add an argument to the function.
     *
     *     $f->accept('a')->type('string');      // function (string $a)
     *     $f->accept('b')->type('int')->var(0); // function (string $a, int $b = 0)
     *
     * @see UserFunction::arg
     * @param $name string argument name
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
     * You can call this several times to accept multiple arguments:
     *
     *     // generates function (string $a, int $b = 0)
     *     $userFunc->arg('$a', 'string')->arg('b', 'int', '0');
     *
     * You have to pass PHP SOURCE CODE to the $default. You can do it easily with
     * `var_export()` for primitive values.
     *
     *     $userFunc->arg('a', 'int', var_export(1, true));
     *
     * @see UserFunction::accept
     * @see UserFunction::argve
     * @param $name string name of the argument.
     * @param $type string type hint of the argument. (optional)
     * @param $default string PHP SOURCE CODE of default value of the argument. (optional)
     */
    public function arg(string $name, string $type = '', string $default = ''): UserFunction
    {
        $this->accept($name)
            ->type($type)
            ->val($default);

        return $this;
    }

    /**
     * Setting function arguments.
     *
     * This is helper method for UserFunction::arg.
     *
     *     // generates function (int $a = 1)
     *     $userFunc->argve('a', 1, 'int');
     *
     * WARNING: Order of arguments between arg and argve is different.
     *
     * @param $name string name of the argument.
     * @param $default string default value of the argument. MUST COMPITABLE WITH var_export().
     * @param $type string type hint of the argument. (optional)
     */
    public function argve(string $name, $default, string $type = ''): UserFunction
    {
        $this->accept($name)
            ->type($type)
            ->var($default);

        return $this;
    }

    /**
     * Define return type
     *
     * @param $type string return type
     */
    public function return(string $type): UserFunction
    {
        $this->returnType = $type;

        return $this;
    }

    /**
     * Append a line of php code to function body.
     *
     * @param $line string php code
     */
    public function line(string $line): UserFunction
    {
        $this->body->line($line);

        return $this;
    }

    /**
     * Append multiple lines of php code to function body.
     *
     * @param $block string array of php codes
     */
    public function block(array $block): UserFunction
    {
        $this->body->line(...$block);

        return $this;
    }

    /**
     * Generate PHP code.
     *
     * By default, it generates minimal codes. You can set $pretty to true to
     * generate PSR2 compitable code format.
     *
     * @param $pretty bool true to generate multi-line code, default to false
     * @param $indent int indent level, used if $pretty is true
     */
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

    private function renderBody(bool $pretty, int $indent): string
    {
        if (!$pretty) {
            return ' {' . $this->body->render() . '}';
        }

        $str = str_repeat(' ', $indent * 4);

        return "\n" . $str . "{\n"
            . $this->body->render($pretty, $indent+1)
            . "\n" . $str . '}';
    }
}
