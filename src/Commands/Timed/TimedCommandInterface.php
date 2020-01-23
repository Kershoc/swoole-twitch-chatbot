<?php

namespace Bot\Commands\Timed;

use Swoole\Coroutine\http\Client;

interface TimedCommandInterface
{
    public function __construct(Client $cli);
    public function run() :void;
}