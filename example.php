<?php

require('vendor/autoload.php');

use Fruit\CompileKit\AnonymousClass as C;
use Fruit\CompileKit\FunctionCall as Call;
use Fruit\CompileKit\Block;
use Fruit\CompileKit\Value;

$b = (new Block)
    ->reqOnce('vendor/autoload.php')
    ->space()
    ->use('PHPUnit\Framework\TestCase', 'TC')
    ->space();

$c = (new C)->extends('TC');
$c
    ->can('testExample')
    ->line('$this->assertTrue(true);');

$b->return(
    (new Call('var_export'))->arg($c)->arg(true)
);

echo '<?php' . "\n" . $b->render(true);
