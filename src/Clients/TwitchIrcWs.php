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
    }

    public function connect(): wsClient
    {
        // Startup, connect to twitch irc and start parsing.
        $cli = new wsClient("irc-ws.chat.twitch.tv", 443, true);
        $ret = $cli->upgrade("/");
        if (!$ret) {
            throw new Exception("Websocket Upgrade Failed", $cli->errCode);
        }
        return $cli;
    }

    public function listen(): void
    {
        while (true) {
            $data = $this->client->recv()->data;
            if ($data) {
                $this->channel->push($data);
            }
        }
    }

    public function start(): wsClient
    {
        $this->client = $this->connect();
        Coroutine::create([$this, 'listen']);
        return $this->client;
    }

    public function login(): void
    {
        $this->client->push("PASS {$_ENV['TWITCH_OAUTH_PASS']}");
        $this->client->push("NICK {$_ENV['TWITCH_NICK']}");
        $this->client->push("CAP REQ :twitch.tv/commands");
        $this->client->push("CAP REQ :twitch.tv/membership");
        $this->client->push("CAP REQ :twitch.tv/tags");
        $this->client->push("JOIN {$_ENV['TWITCH_ROOM']}");
    }
}
