<?php

namespace Bot\Clients;

use Exception;
use Swoole\Coroutine;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\http\Client as wsClient;

class TwitchIrcWs
{
    public $client;
    public $channel;

    public function __construct(Channel $channel)
    {
        $this->channel = $channel;
        Coroutine::create([$this, 'start']);
    }

    public function start()
    {
        // Startup, connect to twitch irc and start parsing.
        $cli = new wsClient("irc-ws.chat.twitch.tv", 443, true);
        $ret = $cli->upgrade("/");
        if (!$ret) {
            throw new Exception("Websocket Upgrade Failed", $cli->errCode);
        }
        $this->client = $cli;

        while (true) {
            $data = $cli->recv()->data;
            if ($data) {
                $this->channel->push($data);
            }
        }
    }
}
