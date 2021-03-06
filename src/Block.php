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
     * Helper to create assignment statement.
     *
     *     // result: '$a = $b;'
     *     Block::assign(Value::as('$a'), Value::as('$b'))->render();
     *
     * @param $to Renderable at left side of assignment.
     * @param $from Renderable at right side of assignment.
     * @return self
     */
    public function assign(Renderable $to, Renderable $from): self
    {
        return $this->append(
            new class($to, $from) implements Renderable {
                private $t;
                private $f;

                public function __construct(Renderable $to, Renderable $from)
                {
                    $this->t = $to;
                    $this->f = $from;
                }

                public function render(bool $p = false, int $i = 0): string
                {
                    if ($i < 0) {
                        $i = 0;
                    }
                    if (!$p) {
                        $i = 0;
                    }
                    $indent = '';
                    if ($p and $i > 0) {
                        $indent = str_repeat(' ', $i * 4);
                    }

                    return $indent . $this->t->render(false, $i)
                        . ' = '
                        . ltrim($this->f->render($p, $i)) . ';';
                }
            }
        );
    }

    /**
     * Helper to convert an expression to statement by appending a colon.
     *
     * @param $r Renderable to render.
     * @return self
     */
    public function stmt(Renderable ...$r): self
    {
        return $this->append(
            new class($r) implements Renderable {
                private $r;

                public function __construct(array $r)
                {
                    $this->r = $r;
                }

                public function render(bool $p = false, int $i = 0): string
                {
                    if ($i < 0) {
                        $i = 0;
                    }
                    $str = '';
                    if ($p) {
                        $str = str_repeat(' ', $i * 4);
                    }

                    $arr = array_map(function (Renderable $r) use ($p, $i) {
                        return ltrim($r->render($p, $i));
                    }, $this->r);

                    return $str . implode(' ', $arr) . ';';
                }
            }
        );
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
