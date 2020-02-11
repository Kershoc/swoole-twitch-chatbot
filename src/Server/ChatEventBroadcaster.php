<?php

namespace Bot\Server;

use Swoole\Coroutine\Channel;
use Swoole\WebSocket\Server;

class ChatEventBroadcaster
{

    private $server;
    private $channel;

    public function __construct(Server $server, Channel $channel)
    {
        $this->server = $server;
        $this->channel = $channel;
    }

    public function run(): void
    {
        while (true) {
            $data = $this->channel->pop();
            if ($data) {
                foreach ($this->server->connections as $fd) {
                    if ($this->server->isEstablished($fd)) {
                        $this->server->push($fd, json_encode($data));
                    }
                }
            }
        }
    }
}
