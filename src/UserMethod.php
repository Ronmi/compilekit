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

    public function render(bool $pretty = false): string
    {
        $ret = parent::render($pretty);
        $prefix = $this->visibility . ' ';
        if ($this->static) {
            $prefix .= 'static ';
        }

        return $prefix . $ret;
    }
}
