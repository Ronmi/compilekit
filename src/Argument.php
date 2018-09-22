<?php

namespace Fruit\CompileKit;

/**
 * Argument represents the definition of a PHP variable, especially for function
 * arguments.
 */
class Argument implements Renderable
{
    private $name;
    private $typeHint = '';
    private $defaultValue;

    public static function __set_state(array $data)
    {
        $ret = new self();
        foreach ($data as $k => $v) {
            $ret->$k = $v;
        }
        return $ret;
    }

    public function __construct(string $name)
    {
        if ($name[0] !== '$') {
            $name = '$' . $name;
        }

        $this->name = $name;
        $this->defaultValue = new Value;
    }

    /**
     * Set type-hinting. Pass empty string to disable type-hinting.
     */
    public function type(string $type): self
    {
        $this->typeHint = $type;
        return $this;
    }

    /**
     * Set default value.
     */
    public function defaults(): self
    {
        return $this->defaultValue;
    }

    /**
     * Helper for Argument::defaults.
     */
    public function rawDefault(string $val): self
    {
        $this->defaults()->raw($val);
        return $this;
    }

    /**
     * Helper for Argument::defaults.
     */
    public function bindDefault($val): self
    {
        $this->defaults()->bind($val);
        return $this;
    }

    /**
     * Helper for Argument::defaults.
     */
    public function setDefault($val): self
    {
        $this->defaults()->set($val);
        return $this;
    }

    /**
     * @see Renderable
     */
    public function render(bool $pretty = false, int $indent = 0): string
    {
        $padding = '';
        if ($pretty and $indent > 0) {
            $padding = str_repeat(' ', $indent * 4);
        }

        $t = $this->typeHint;
        if ($t !== '') {
            $t .= ' ';
        }

        $d = $this->defaultValue->render();
        if ($d !== '') {
            $d = ' = ' . $d;
        }

        return $padding . $t . $this->name . $d;
    }
}
