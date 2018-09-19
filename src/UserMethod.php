<?php

namespace Fruit\CompileKit;

class UserMethod extends UserFunction
{
    private $visibility;
    private $static;

    public function __construct(
        string $name,
        string $vis = 'public',
        bool $static = false,
        bool $pretty = false
    ) {
        parent::__construct($name, $pretty);
        $this->visibility = $vis;
        $this->static = $static;
    }

    public function __toString(): string
    {
        $ret = parent::__toString();
        $prefix = $this->visibility . ' ';
        if ($this->static) {
            $prefix .= 'static ';
        }

        return $prefix . $ret;
    }
}
