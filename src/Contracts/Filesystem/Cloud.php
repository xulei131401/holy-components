<?php

namespace Holy\Contracts\Filesystem;

interface Cloud extends Filesystem
{
    public function url($path);
}
