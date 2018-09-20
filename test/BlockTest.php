<?php

namespace FruitTest\CompileKit;

use Fruit\CompileKit\Block;

class BlockTest extends \PHPUnit\Framework\TestCase
{
    public function testEmpty()
    {
        $f = new Block();
        $expect = '';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testSimple()
    {
        $f = (new Block())->line('$a=1;', '$b=$a;');
        $expect = '$a=1;$b=$a;';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testPretty()
    {
        $f = (new Block())->line('$a=1;', '$b=$a;');
        $expect = '$a=1;
$b=$a;';
        $actual = $f->render(true);
        $this->assertEquals($expect, $actual);
    }

    public function testPrettyIndent()
    {
        $f = (new Block())->line('$a=1;', '$b=$a;');
        $expect = '    $a=1;
    $b=$a;';
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual);
    }
}
