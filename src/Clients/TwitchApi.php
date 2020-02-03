<?php

// TODO: Caching. All these API calls need to be cached
// TODO: Fill in the API blanks. right now this is super basic for proof of concept

namespace Bot\Clients;

class TwitchApi
{
    private $endpointUrl = 'https://api.twitch.tv/helix/';
    private $clientId = '';

    public function __construct()
    {
        $this->clientId = $_ENV['TWITCH_CLIENT_ID'];
    }

    public function getUserById($userId)
    {
        $opts = array(
            'http' => array(
                'method' => "GET",
                'header' => "Client-ID: " . $this->clientId . "\r\n"
            )
        );
        $context = stream_context_create($opts);
        $url = $this->endpointUrl . 'users?id=' . $userId;
        $data = json_decode(file_get_contents($url, false, $context), true);
        return $data['data'];
    }
}
