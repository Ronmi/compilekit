<?php

namespace Fruit\CompileKit;

class UserMethod extends UserFunction
{
    private $visibility;
    private $static;

    public function __construct(
        string $name,
        string $vis = 'public',
        bool $static = false
    ) {
        parent::__construct($name);
        $this->visibility = $vis;
        $this->static = $static;
    }

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
