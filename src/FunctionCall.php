<?php

namespace Fruit\CompileKit;

/**
 * FunctionCall denotes a function-call expression.
 *
 * Unlike UserFunction and similar classes, which generates php statement,
 * the FunctionCall class generates an expression. You have to append a colon to
 * generated code to change it a valid statement.
 */
class FunctionCall implements Renderable
{
    private $name;
    private $args = [];

    /**
     * Provide readonly access to function name.
     */
    public function __get(string $name)
    {
        if ($name === 'name') {
            return $this->name;
        }

        trigger_error($name . ' is not a valid property of FunctionCall.');
    }

    public static function __set_state(array $data)
    {
        $ret = new self('');
        foreach ($data as $k => $v) {
            $ret->$k = $v;
        }
        return $ret;
    }

    public function __construct(string $target)
    {
        $this->name = $target;
    }

    /**
     * Set the arguments to pass.
     *
     *     (new FunctionCall('a')->rawArg('1', '"asd"'); // a(1, "asd")
     *
     * @see FunctionCall::arg
     * @param $vals string[] php codes to pass.
     * @return self
     */
    public function rawArg(string ...$vals): self
    {
        foreach ($vals as $v) {
            array_push($this->args, Value::as($v));
        }

        return $this;
    }

    /**
     * Set the arguments to pass.
     *
     * Arguments passed here will be converted to string using Value::of().
     *
     *     (new FunctionCall('a')->arg(1, 'asd'); // a(1, 'asd');
     *
     * @see FunctionCall::rawArg
     * @param $vals string[] php codes to pass.
     * @return self
     */
    public function arg(...$vals): self
    {
        foreach ($vals as $v) {
            array_push($this->args, Value::of($v));
        }

        return $this;
    }

    /**
     * @see Renderable
     * @return string of generated php code.
     */
    public function render(bool $pretty = false, int $indent = 0): string
    {
        $str = '';
        $lf = '';
        if ($pretty) {
            if ($indent < 0) {
                $indent = 0;
            }
            $str = str_repeat(' ', $indent * 4);
            $lf = "\n";
        }

        $argc = count($this->args);
        $i = 0;
        if ($argc > 4) {
            $i = $indent + 1;
        }
        $args = array_map(function ($a) use ($pretty, $i, $indent) {
            $str = '';
            if ($pretty) {
                $str = str_repeat(' ', $i * 4);
            }

            if ($a instanceof Renderable) {
                return $str . ltrim($a->render($pretty, $indent+1));
            }

            return $str . $a;
        }, $this->args);

        if (count($args) < 1) {
            return $str . $this->name . '()';
        }

        $sp = ', ';
        $lfArg = '';
        $strArg = '';
        if ($pretty and $argc > 4) {
            $sp = ',' . $lf;
            $lfArg = $lf;
            $strArg = $str;
        }

        return $str . $this->name . '('
            . $lfArg . implode($sp, $args)
            . $lfArg . $strArg . ')';
    }
}
