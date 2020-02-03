<?php

namespace Bot\Commands\Timed;

use Swoole\Coroutine\Channel;
use Swoole\Coroutine\http\Client;

interface TimedCommandInterface
{
    public function __construct(Client $cli, Channel $eventBroadcaster);
    public function run(): void;
}
