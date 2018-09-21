# CompileKit

This package is part of Fruit Framework, requires PHP 7+.

CompileKit is a set of classes to dynamically generate PHP codes.

# Synopsis

```php
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
```

will print

```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

use PHPUnit\Framework\TestCase as TC;

return var_export(
    new class(
    ) extends TC
    {
        public function testExample()
        {
            $this->assertTrue(true);
        }
    },
    true
);
```

# License

Any version of MIT, GPL or LGPL.
