<?php

namespace Fruit\CompileKit;

/**
 * Block denotes multiple lines of php code.
 */
class Block implements Renderable
{
    private $body = [];
    private $renderHeader = '';

    public static function __set_state(array $data)
    {
        $ret = new self;
        $ret->body = $data['body'];
        $ret->renderHeader = $data['renderHeader'];
        return $ret;
    }

    /**
     * set this block as a php source file.
     *
     * By calling this method, Block::render will return php open tag at beginning.
     *
     * @return self
     */
    public function asFile(): self
    {
        $this->renderHeader = '<?php' . "\n";
        return $this;
    }

    /**
     * set this block as a php script file
     *
     * By calling this method, Block::render will return hashbang and php open tag
     * at beginning.
     *
     * @return self
     */
    public function asScript(): self
    {
        $this->renderHeader = "#!/usr/bin/env php\n" .
            '<?php' . "\n";
        return $this;
    }

    /**
     * Append a line of php code to this code block.
     *
     * @param $lines string[] php codes
     * @return self
     */
    public function line(string ...$lines): self
    {
        foreach ($lines as $line) {
            array_push($this->body, $line);
        }

        return $this;
    }

    /**
     * A helper to add empty line.
     *
     * @return self
     */
    public function space(): self
    {
        return $this->line('');
    }

    /**
     * Append another block of code to this block.
     *
     * @param $blocks Renderable[] php codes
     * @return self
     */
    public function append(Renderable ...$blocks): self
    {
        foreach ($blocks as $block) {
            array_push($this->body, $block);
        }

        return $this;
    }

    /**
     * Add another Renderable as child block, which forces one more indent level.
     *
     * @return self
     */
    public function child(Renderable $child): self
    {
        return $this->append(
            new class($child) implements Renderable {
                private $r;

                public function __construct(Renderable $r)
                {
                    $this->r = $r;
                }

                public function render(bool $p = false, int $i = 0): string
                {
                    if ($i < 0) {
                        $i = 0;
                    }
                    return $this->r->render($p, $i+1);
                }
            }
        );
    }

    /**
     * @see Renderable
     * @return string of generated php code.
     */
    public function render(bool $pretty = false, int $lv = 0): string
    {
        if (!$pretty) {
            return $this->renderHeader . implode('', array_map(function ($b) {
                if ($b instanceof Renderable) {
                    return $b->render(false);
                }

                return $b;
            }, $this->body));
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

        return $this->renderHeader . substr($ret, 1);
    }

    /**
     * Helper to add namespace statement.
     *
     * @param $namespace string namespace.
     */
    public function ns(string $namespace): self
    {
        return $this->line('namespace ' . $namespace . ';');
    }

    /**
     * Helper to add use statement.
     *
     * @param $cls string full class name to use.
     * @param $as string use it as. (optional)
     */
    public function use(string $cls, string $as = ''): self
    {
        $str = 'use ' . $cls;
        if ($as !== '') {
            $str .= ' as ' . $as;
        }

        return $this->line($str . ';');
    }

    /**
     * Helper to add return statement.
     */
    public function return(Renderable $value): self
    {
        return $this->append(Value::stmt(
            Value::as('return'),
            $value
        ));
    }

    /**
     * Helper to add require(__DIR__ . 'file') statement
     *
     *     $b->req('vendor/autoload.php');
     *     // require(__DIR__ . 'vendor/autoload.php');
     *
     * @param $file string file path.
     */
    public function req(string $file): self
    {
        if ($file[0] !== '/') {
            $file = '/' . $file;
        }
        return $this->line(
            (new FunctionCall('require'))
            ->rawArg('__DIR__ . ' . Value::of($file)->render())
            ->render() . ';'
        );
    }

    /**
     * Helper to add require_once(__DIR__ . 'file') statement
     *
     *     $b->reqOnce('vendor/autoload.php');
     *     // require_once(__DIR__ . 'vendor/autoload.php');
     *
     * @param $file string file path.
     */
    public function reqOnce(string $file): self
    {
        if ($file[0] !== '/') {
            $file = '/' . $file;
        }
        return $this->line(
            (new FunctionCall('require_once'))
            ->rawArg('__DIR__ . ' . Value::of($file)->render())
            ->render() . ';'
        );
    }

    /**
     * Helper to add require('file') statement
     *
     *     $b->reqAbs('vendor/autoload.php');
     *     // require('vendor/autoload.php');
     *
     * @param $file string file path.
     */
    public function reqAbs(string $file): self
    {
        return $this->line(
            (new FunctionCall('require'))
            ->arg($file)
            ->render() . ';'
        );
    }

    /**
     * Helper to add require('file') statement
     *
     *     $b->reqOnceAbs('vendor/autoload.php');
     *     // require_once('vendor/autoload.php');
     *
     * @param $file string file path.
     */
    public function reqOnceAbs(string $file): self
    {
        return $this->line(
            (new FunctionCall('require_once'))
            ->arg($file)
            ->render() . ';'
        );
    }
}
