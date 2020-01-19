<?php

namespace Bot\Handlers;

use Bot\MessageObject;
use Co\http\Client;

class Privmsg implements HandlerInterface
{
    private $cli;

    public function __construct(Client $cli)
    {
        $this->cli = $cli;
    }

    public function handle(MessageObject $message_object) :void
    {
        // In a PRIVMSG the options are our chat message
        $message = trim($message_object->options, ": \t\n\r\0\x0B"); // Adding colon to trim list to get it off the front. We've got to trim the newline off the end, so might as well

        if ($message[0] === "!") {
            // Bot Command, see if we have one to match
            $has_args = strpos($message," ");
            if ($has_args !== false) {
                $command = substr($message, 1, $has_args - 1);
            } else {
                $command = substr($message, 1);
            }
            $command_class = "Bot\\Commands\\" . ucwords(strtolower($command) . "Command");
            if (class_exists( $command_class )) {
                $cmd = new $command_class($this->cli);
                $cmd->run($message_object);
            }
        }
    }
}