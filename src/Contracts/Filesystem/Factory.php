<?php

namespace Holy\Contracts\Filesystem;

interface Factory
{
    public function disk($name = null);
}
