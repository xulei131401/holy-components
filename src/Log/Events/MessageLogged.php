<?php

namespace Holy\Log\Events;

class MessageLogged
{
    public $level;

    public $message;

    public $context;

    /**
     * MessageLogged constructor.
     * @param $level
     * @param $message
     * @param array $context
     */
    public function __construct($level, $message, array $context = [])
    {
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
    }
}
