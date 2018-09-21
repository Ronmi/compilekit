<?php

namespace Fruit\CompileKit;

/**
 * UserMethod is a helper to define your own method.
 *
 * @see UserFunction
 */
class UserMethod extends UserFunction
{
    private $visibility;
    private $static;

    public static function __set_state(array $data)
    {
        $ret = new self($data['name'], $data['visibility'], $data['static']);
        $ret->restore($data);
        return $ret;
    }

    /**
     * The constructor.
     *
     * Pass the name of the method here. Method name and visibility is NOT
     * validated, might be supported in later version.
     *
     * @param $name string method name.
     * @param $vis string visibility, default to public.
     * @param $static bool true if it is a static method, default to false.
     */
    public function __construct(
        string $name,
        string $vis = 'public',
        bool $static = false
    ) {
        parent::__construct($name);
        $this->visibility = $vis;
        $this->static = $static;
    }

    /**
     * @see UserFunction::render
     */
    public function render(bool $pretty = false, int $indent = 0): string
    {
        if ($indent < 0) {
            $indent = 0;
        }

        $ret = parent::render($pretty, $indent);
        $prefix = $this->visibility . ' ';
        if ($this->static) {
            $prefix .= 'static ';
        }

        if ($pretty and $indent > 0) {
            $ret = substr($ret, $indent * 4);
        }
        return str_repeat(' ', $indent * 4) . $prefix . $ret;
    }
}
