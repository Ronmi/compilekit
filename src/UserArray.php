<?php

namespace Fruit\CompileKit;

use ArrayAccess;

/**
 * UserArray represents an array. It supports both numerical and associative arrays.
 */
class UserArray implements Renderable, ArrayAccess
{
    private $values;
    private $keys;

    /**
     * Constructor, shorthane of `(new UserArray)->from($arr)`
     *
     * @param $arr arror of source elements, default to empty array.
     */
    public function __construct(array $arr = [])
    {
        $this->from($arr);
    }

    /**
     * ArrayAccess
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->keys);
    }

    /**
     * ArrayAccess
     *
     * NOTE: This method always return Value instance if element exists.
     *
     * @return Value instance.
     */
    public function offsetGet($offset): Value
    {
        return $this->values[$offset];
    }

    /**
     * ArrayAccess
     */
    public function offsetSet($offset, $value)
    {
        $this->keys[$offset] = Value::of($offset);
        $this->values[$offset] = Value::of($value);
    }

    /**
     * ArrayAccess
     */
    public function offsetUnset($offset)
    {
        unset($this->keys[$offset]);
        unset($this->values[$offset]);
    }

    /**
     * (Re)initialize this renderable with the array.
     *
     * Keys and values from $arr is converted to Renderble using Value::of.
     *
     * @param $arr array to use.
     * @return self
     */
    public function from(array $arr): self
    {
        $this->values = [];
        $this->keys = [];
        foreach ($arr as $k => $v) {
            $this[$k] = $v;
        }

        return $this;
    }

    /**
     * @see Renderable
     * @return string of generated php code.
     */
    public function render(bool $pretty = false, int $indent = 0): string
    {
        if ($indent < 0) {
            $indent = 0;
        }

        $lf = '';
        $str = '';
        if ($pretty) {
            $lf = "\n";
            $str = str_repeat(' ', $indent * 4);
        }

        $ret = $str . '[' . $lf;

        foreach ($this->keys as $id => $k) {
            $v = $this->values[$id];
            $ret .= $k->render($pretty, $indent + 1)
                . ' => '
                . $v->render($pretty, 0) . ',' . $lf;
        }
        if (!$pretty) {
            $ret = substr($ret, 0, strlen($ret)-1);
        }

        return $ret . $str . ']';
    }
}
