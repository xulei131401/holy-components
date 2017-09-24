<?php
namespace Holy\Log;

use Monolog\Logger as Monolog;

class Logger
{

    public function createLogger()
    {
        $log = new Writer(
            new Monolog($this->channel())
        );
        return $log;

    }

    protected function channel()
    {
        return 'production';
    }
}