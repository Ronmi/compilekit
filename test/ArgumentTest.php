<?php

namespace FruitTest\CompileKit;

use Fruit\CompileKit\Argument as A;

class ArguemntTest extends \PHPUnit\Framework\TestCase
{
    public function allP(): array
    {
        return [
            [
                new A('a'),
                '$a',
                '    $a',
                'basic without type hint and default value'
            ],
            [
                (new A('a'))->type('string'),
                'string $a',
                '    string $a',
                'type hinted but no default value'
            ],
            [
                (new A('a'))->val('1'),
                '$a = 1',
                '    $a = 1',
                'default value without type'
            ],
            [
                (new A('a'))->var(1),
                '$a = 1',
                '    $a = 1',
                'default value without type (var_exported)'
            ],
            [
                (new A('a'))->type('int')->val('1'),
                'int $a = 1',
                '    int $a = 1',
                'all-in-one'
            ],
        ];
    }

    /**
     * @dataProvider allP
     */
    public function testNoIndent(
        A $arg,
        string $expect,
        string $indented,
        string $msg
    ) {
        $actual = $arg->render();
        $this->assertEquals($expect, $actual, $msg);

        $actual = $arg->render(true, 1);
        $this->assertEquals($indented, $actual, $msg);
    }
}
