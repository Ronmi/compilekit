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

    public function testAppend()
    {
        $f = (new Block)
            ->line('$a=1;')
            ->append((new Block)->line('$b=$a;'));
        $expect = '    $a=1;
    $b=$a;';
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual);
    }

    public function testNS()
    {
        $f = (new Block)->ns('A\B\C');
        $expect = 'namespace A\B\C;';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testUse()
    {
        $f = (new Block)->use('A\B\C')->use('D\E\F', 'X');
        $expect = 'use A\B\C;use D\E\F as X;';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testReq()
    {
        $f = (new Block)->req('vendor/autoload.php');
        $expect = "require(__DIR__ . 'vendor/autoload.php');";
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testReqOnce()
    {
        $f = (new Block)->reqOnce('vendor/autoload.php');
        $expect = "require_once(__DIR__ . 'vendor/autoload.php');";
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testReqAbs()
    {
        $f = (new Block)->reqAbs('vendor/autoload.php');
        $expect = "require('vendor/autoload.php');";
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testReqOnceAbs()
    {
        $f = (new Block)->reqOnceAbs('vendor/autoload.php');
        $expect = "require_once('vendor/autoload.php');";
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }
}
