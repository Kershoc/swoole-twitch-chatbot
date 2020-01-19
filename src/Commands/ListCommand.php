<?php

namespace Bot\Commands;

use Bot\MessageObject;
use Co\http\Client;

class ListCommand implements CommandInterface
{
    private $cli;

    public function __construct(Client $cli)
    {
        $this->cli = $cli;
    }

    public function run(MessageObject $message_object) :void
    {
        $this->cli->push("PRIVMSG {$message_object->irc_room} :Available commands are !speak and !dice");
    }


}