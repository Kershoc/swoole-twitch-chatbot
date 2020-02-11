<?php

namespace Bot\Server;

use DirectoryIterator;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Http\Client;
use Swoole\WebSocket\Server;

class TimedCommandRunner
{
    private $server;
    private $client;
    private $channel;

    public function __construct(Server $server, Client $client, Channel $channel)
    {
        $this->server = $server;
        $this->client = $client;
        $this->channel = $channel;
    }

    public function run(): void
    {
        foreach (new DirectoryIterator('src/Commands/Timed/') as $item) {
            $class = 'Bot\\Commands\\Timed\\' . $item->getBasename('.php');
            if (class_exists($class)) {
                $timedCommand = new $class($this->client, $this->channel);
                $this->server->tick($timedCommand->repeatAfter, [$timedCommand, 'run']);
                echo "[" . date("Y-m-d H:i:s") . "] {$class} Timer Started! {$timedCommand->repeatAfter}ms interval \n";
            }
        }
    }
}
