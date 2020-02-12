<?php

// TODO: Caching. All these API calls need to be cached
// TODO: Fill in the API blanks. right now this is super basic for proof of concept

namespace Bot\Clients;

use Swoole\Coroutine\http\Client;

class TwitchApi
{
    private $endpointPath = '/helix/';
    private $endpointHost = 'api.twitch.tv';
    private $clientId = '';
    private $client;

    public function __construct()
    {
        $this->clientId = $_ENV['TWITCH_CLIENT_ID'];
    }

    public function connect(): self
    {
        $client = new Client($this->endpointHost, 443, true);
        $client->setHeaders(['Client-ID' => $this->clientId]);
        $this->client = $client;
        return $this;
    }

    public function getUserById($userId)
    {
        $url = $this->endpointPath . 'users?id=' . $userId;
        $this->client->get($url);
        $data = json_decode($this->client->body, true);
        return $data['data'];
    }
}
