<?php

declare(strict_types=1);

/**
 * @package BENLocator
 */

namespace BENInc;

class Activate
{
    public static function activate()
    {

        flush_rewrite_rules();
    }
}
