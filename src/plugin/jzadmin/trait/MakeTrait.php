<?php

namespace plugin\jzadmin\trait;

trait MakeTrait
{
    public static function make()
    {
        return new static(...func_get_args());
    }
}
