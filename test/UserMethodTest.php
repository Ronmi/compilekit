<?php

namespace FruitTest\CompileKit;

use Fruit\CompileKit\UserMethod;

class UserMethodTest extends \PHPUnit\Framework\TestCase
{
    public function testBody()
    {
        $f = (new UserMethod('f'))
            ->line('$x = 1;')
            ->block(['return $x;']);
        $expect = 'public function f() {$x = 1;return $x;}';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testBodyPrettier()
    {
        $f = (new UserMethod('f', 'public', false))
            ->line('$x = 1;')
            ->block(['return $x;']);
        $expect = 'public function f()
{
    $x = 1;
    return $x;
}';
        $actual = $f->render(true);
        $this->assertEquals($expect, $actual);
    }

    public function testBodyPrettierIndent()
    {
        $f = (new UserMethod('f', 'public', false))
            ->line('$x = 1;')
            ->block(['return $x;']);
        $expect = '    public function f()
    {
        $x = 1;
        return $x;
    }';
        $actual = $f->render(true, 1);
        $this->assertEquals($expect, $actual);
    }

    public function testArgs()
    {
        $f = (new UserMethod('f'))
            ->arg('arg1')
            ->arg('$arg2')
            ->arg('$arg3', 'string')
            ->arg('$arg4', '', 'null')
            ->arg('arg5', 'string', '"test"');
        $expect = 'public function f($arg1,$arg2,string $arg3,$arg4 = null,string $arg5 = "test") {}';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testReturnType()
    {
        $f = (new UserMethod('f'))
            ->return('string');
        $expect = 'public function f(): string {}';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testVisibility()
    {
        $f = (new UserMethod('f', 'private'))
            ->return('string');
        $expect = 'private function f(): string {}';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }

    public function testStatic()
    {
        $f = (new UserMethod('f', 'private', true))
            ->return('string');
        $expect = 'private static function f(): string {}';
        $actual = $f->render();
        $this->assertEquals($expect, $actual);
    }
}
