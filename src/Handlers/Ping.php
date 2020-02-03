<?php

namespace Bot\Handlers;

use Bot\MessageObject;
use Swoole\Coroutine\http\Client;

class Ping implements HandlerInterface
{
    private $cli;

    public function __construct(Client $cli)
    {
        $this->cli = $cli;
    }

    public function handle(MessageObject $messageObject): void
    {
        echo "> PONG :tmi.twitch.tv \n";
        $this->cli->push('PONG :tmi.twitch.tv');
    }
}
