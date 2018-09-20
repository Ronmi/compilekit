<?php

namespace Fruit\CompileKit;

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
            if (trim($line) !== '') {
                $buf .= $indent . $line;
            }
            $ret .= $buf;
        }

        return substr($ret, 1);
    }
}
