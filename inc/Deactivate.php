<?php

declare(strict_types=1);

/**
 * @package BENLocator
 */

namespace BENInc;

class Deactivate
{
    public static function deactivate()
    {

        flush_rewrite_rules();
    }
}
