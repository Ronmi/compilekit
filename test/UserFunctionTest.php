<?php

namespace FruitTest\CompileKit;

use Fruit\CompileKit\UserFunction;

class UserFunctionTest extends \PHPUnit\Framework\TestCase
{
    public function testAnonymous()
    {
        $f = new UserFunction();
        $expect = 'function () {}';
        $actual = $f->__toString();
        $this->assertEquals($expect, $actual);
    }

    public function testBody()
    {
        $f = (new Userfunction())
            ->line('$x = 1;')
            ->block(['return $x;']);
        $expect = 'function () {$x = 1;return $x;}';
        $actual = $f->__toString();
        $this->assertEquals($expect, $actual);
    }

    public function testBodyPrettier()
    {
        $f = (new Userfunction('', true))
            ->line('$x = 1;')
            ->block(['return $x;']);
        $expect = 'function ()
{
    $x = 1;
    return $x;
}';
        $actual = $f->__toString();
        $this->assertEquals($expect, $actual);
    }

    public function testName()
    {
        $f = new Userfunction('f');
        $expect = 'function f() {}';
        $actual = $f->__toString();
        $this->assertEquals($expect, $actual);
    }

    public function testArgs()
    {
        $f = (new Userfunction())
            ->arg('arg1')
            ->arg('$arg2')
            ->arg('$arg3', 'string')
            ->arg('$arg4', '', 'null')
            ->arg('arg5', 'string', '"test"');
        $expect = 'function ($arg1, $arg2, string $arg3, $arg4 = null, string $arg5 = "test") {}';
        $actual = $f->__toString();
        $this->assertEquals($expect, $actual);
    }

    public function testReturnType()
    {
        $f = (new Userfunction())
            ->return('string');
        $expect = 'function (): string {}';
        $actual = $f->__toString();
        $this->assertEquals($expect, $actual);
    }
}
