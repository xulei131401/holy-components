<?php

namespace Holy\Components\Database\Factory;

interface ConnectionResolverInterface
{
    public function connection($name = null);

    public function getDefaultConnection();

    public function setDefaultConnection($name);
}
