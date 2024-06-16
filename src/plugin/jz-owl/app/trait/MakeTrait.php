<?php

namespace plugin\jzadmin\app\trait;

trait MakeTrait
{
    public static function make(): static
    {
        return new static(...func_get_args());
    }
}
