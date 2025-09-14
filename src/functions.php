<?php

use juniyasyos\ShieldLite\Helpers\ShieldHelper;

if (!function_exists('shield5f82b3b72f2a9')) {
    if (!function_exists('shield')) {
        function shield()
        {
            return new ShieldHelper;
        }
    }
}

// Optional backward-compat helper alias
if (!function_exists('hexa')) {
    function hexa()
    {
        return shield();
    }
}
