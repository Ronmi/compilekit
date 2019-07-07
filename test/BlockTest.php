<?php

namespace FruitTest\CompileKit;

use Fruit\CompileKit\Block;
use Fruit\CompileKit\Value;
use Fruit\CompileKit\FunctionCall;

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

    public function testChild()
    {
        $f = (new Block)->line('$a=1;')->child((new Block())->line('$b=$a;'));
        $expect = '$a=1;$b=$a;';
        $actual = $f->render();
        $this->assertEquals($expect, $actual, 'no format');

        $expect = '$a=1;
    $b=$a;';
        $actual = $f->render(true);
        $this->assertEquals($expect, $actual, 'pretty');

        $expect = '    $a=1;
        $b=$a;';
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual, 'indent');
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

    public function testReturn()
    {
        $v = (new Block)->line('1+1');
        $b = (new Block)->return($v);
        $expect = 'return 1+1;';
        $actual = $b->render();
        $this->assertEquals($expect, $actual);
    }

    public function testReq()
    {
        $f = (new Block)->req('vendor/autoload.php');
        $expect = "require(__DIR__ . '/vendor/autoload.php');";
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testReqOnce()
    {
        $f = (new Block)->reqOnce('vendor/autoload.php');
        $expect = "require_once(__DIR__ . '/vendor/autoload.php');";
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

    public function testAsFile()
    {
        $f = (new Block)
            ->reqOnceAbs('vendor/autoload.php')
            ->asFile();
        $expect = '<?php' . "\nrequire_once('vendor/autoload.php');";
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testAsScript()
    {
        $f = (new Block)
            ->reqOnceAbs('vendor/autoload.php')
            ->asScript();
        $expect = '#!/usr/bin/env php' . "\n"
            . '<?php' . "\nrequire_once('vendor/autoload.php');";
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testStmt()
    {
        $f = (new Block)->stmt(Value::as('return'), new FunctionCall('a'));
        $expect = '    return a();';
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual);
    }

    public function testStmtUgly()
    {
        $f = (new Block)->stmt(Value::as('return'), (new FunctionCall('a'))->arg(1));
        $expect = 'return a(1);';
        $actual = $f->render(false, 1);
        $this->assertEquals($expect, $actual);

        $f = (new Block)->stmt(Value::as('return'), Value::ugly((new FunctionCall('a'))->arg(1)));
        $expect = '    return a(1);';
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual);
    }

    public function testAssign()
    {
        $v = (new Block)->assign(
            Value::as('$a'),
            Value::of(1)
        );
        $expect = '    $a = 1;';
        $actual = $v->render(true, 1);
        $this->assertEquals($expect, $actual);
    }

    public function testAssignUgly()
    {
        $v = (new Block)->assign(
            Value::as('$a'),
            Value::of(1)
        );
        $expect = '$a = 1;';
        $actual = $v->render(false, 1);
        $this->assertEquals($expect, $actual);

        $v = (new Block)->assign(
            Value::ugly(Value::as('$a')),
            Value::ugly((new FunctionCall('new DateTime'))->arg('2019-07-01 00:00:00'))
        );
        $actual = $v->render(true, 1);
        $expect = '    $a = new DateTime(\'2019-07-01 00:00:00\');';
        $this->assertEquals($expect, $actual);
    }
}
