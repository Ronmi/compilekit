<?php

namespace Fruit\CompileKit;

/**
 * Renderable defines an object that can "render" its content to valid php code.
 *
 * * Generated code MUST obey PSR2 when $pretty is setted to true, prefix with four
 *   space characters per indent level.
 * * Code format when $pretty === false is unspecified.
 * * User-provided content is not considered as "generated code".
 */
interface Renderable
{
    public function render(bool $pretty = false, int $indent = 0);
}
