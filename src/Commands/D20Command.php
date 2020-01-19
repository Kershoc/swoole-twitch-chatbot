<?php

namespace Bot\Commands;

use Bot\MessageObject;
use Co\http\Client;

class D20Command implements CommandInterface
{
    private $cli;

    public function __construct(Client $cli)
    {
        $this->cli = $cli;
    }

    public function run(MessageObject $message_object) :void
    {
        $num = rand(1,20);
        if ($num === 20) {
            $this->cli->push("PRIVMSG {$message_object->irc_room} :" . $message_object->tags['display-name'] . ' CRITS! ' . $num);
        } elseif ($num === 1) {
            $this->cli->push("PRIVMSG {$message_object->irc_room} :" . $message_object->tags['display-name'] . 'rolls ' . $num . ' Critical Fail!');
        } else {
            $this->cli->push("PRIVMSG {$message_object->irc_room} :" . $message_object->tags['display-name'] . ' has rolled a ' . $num);
        }
    }


}