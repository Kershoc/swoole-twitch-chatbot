<?php

namespace Bot\Commands;

use Bot\MessageObject;
use Co\http\Client;

class D6Command implements CommandInterface
{
    private $cli;

    public function __construct(Client $cli)
    {
        $this->cli = $cli;
    }

    public function run(MessageObject $message_object) :void
    {
        $this->cli->push("PRIVMSG {$message_object->irc_room} :" . $message_object->tags['display-name'] . ' has rolled a ' . rand(1,6));
    }


}