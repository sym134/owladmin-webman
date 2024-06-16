<?php

namespace plugin\owladmin\event;

use Illuminate\Foundation\Events\Dispatchable;

class ExtensionChanged{
    use Dispatchable;
    public function __construct(
        public string $name,
        public string $type
    )
    {

    }
}
