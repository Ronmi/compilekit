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
     */
    public function arg(...$vals): self
    {
        foreach ($vals as $v) {
            array_push($this->args, Value::of($v));
        }

        return $this;
    }

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

        $args = array_map(function ($a) use ($pretty, $indent) {
            if ($a instanceof Renderable) {
                return $a->render($pretty, $indent+1);
            }

            $str = '';
            if ($pretty) {
                $str = str_repeat(' ', ($indent + 1) * 4);
            }

            return $str . $a;
        }, $this->args);

        if (count($args) < 1) {
            return $str . $this->name . '()';
        }

        return $str . $this->name . '('
            . $lf . implode(',' . $lf, $args)
            . $lf . $str . ')';
    }
}
