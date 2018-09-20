<?php

namespace Fruit\CompileKit;

/**
 * Block denotes multiple lines of php code.
 */
class Block implements Renderable
{
    private $body = [];

    /**
     * Append a line of php code to this code block.
     *
     * @param $lines string[] php codes
     */
    public function line(string ...$lines): Block
    {
        foreach ($lines as $line) {
            array_push($this->body, $line);
        }

        return $this;
    }

    /**
     * A helper to add empty line.
     */
    public function space(): Block
    {
        return $this->line('');
    }

    /**
     * Append another block of code to this block.
     *
     * @param $blocks Renderable[] php codes
     */
    public function append(Renderable ...$blocks): Block
    {
        foreach ($blocks as $block) {
            array_push($this->body, $block);
        }

        return $this;
    }

    /**
     * @see Renderable
     */
    public function render(bool $pretty = false, int $lv = 0): string
    {
        if (!$pretty) {
            return implode('', $this->body);
        }

        if ($lv < 0) {
            $lv = 0;
        }
        $indent = str_repeat(' ', $lv * 4);

        $ret = '';
        foreach ($this->body as $line) {
            $buf = "\n";
            if ($line instanceof Renderable) {
                $buf .= $line->render(true, $lv);
            } elseif (trim($line) !== '') {
                $buf .= $indent . $line;
            }
            $ret .= $buf;
        }

        return substr($ret, 1);
    }

    /**
     * Helper to add namespace statement.
     *
     * @param $namespace string namespace.
     */
    public function ns(string $namespace): Block
    {
        return $this->line('namespace ' . $namespace . ';');
    }

    /**
     * Helper to add use statement.
     *
     * @param $cls string full class name to use.
     * @param $as string use it as. (optional)
     */
    public function use(string $cls, string $as = ''): Block
    {
        $str = 'use ' . $cls;
        if ($as !== '') {
            $str .= ' AS ' . $as;
        }

        return $this->line($str . ';');
    }
}
