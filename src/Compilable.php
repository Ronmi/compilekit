<?php

namespace Fruit\CompileKit;

/**
 * Compilable repesents some object which is capable to compile its content to PHP
 * code.
 */
interface Compilable
{
    public function compile(): Renderable;
}
