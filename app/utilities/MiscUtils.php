<?php

namespace App\Utilities;

class MiscUtils
{
    public function __construct()
    {
    }

    public static function randomHexColor()
    {
        $hexColorPart = function () {
            return str_pad(dechex(random_int(0, 255)), 2, '0', STR_PAD_LEFT);
        };

        return strtoupper($hexColorPart() . $hexColorPart() . $hexColorPart());
    }
}