<?php

namespace Bot\Commands;

use Bot\EventObject;
use Bot\MessageObject;
use Swoole\Coroutine\http\Client;
use Swoole\Coroutine\Channel;

class D20Command implements CommandInterface
{
    private $cli;
    private $broadcaster;

    public function __construct(Client $cli, Channel $eventBroadcaster)
    {
        $this->cli = $cli;
        $this->broadcaster = $eventBroadcaster;
    }

    public function run(MessageObject $message_object) :void
    {
        $num = rand(1,20);
        if ($num === 20) {
            $this->cli->push("PRIVMSG {$message_object->irc_room} :" . $message_object->tags['display-name'] . ' CRITS! ' . $num);
        } elseif ($num === 1) {
            $this->cli->push("PRIVMSG {$message_object->irc_room} :" . $message_object->tags['display-name'] . ' rolls ' . $num . ' Critical Fail!');
        } else {
            $this->cli->push("PRIVMSG {$message_object->irc_room} :" . $message_object->tags['display-name'] . ' has rolled a ' . $num);
        }

        $payload = [
            'command' => 'd20',
            'result' => $num,
            'user' => $message_object->tags['display-name'],
            ];
        $eventObj = new EventObject('popup', $payload);
        $this->broadcaster->push($eventObj);
    }


}