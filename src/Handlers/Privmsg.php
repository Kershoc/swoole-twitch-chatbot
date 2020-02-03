<?php

namespace Bot\Handlers;

use Bot\Clients\TwitchApi;
use Bot\EventObject;
use Bot\MessageObject;
use Co\http\Client;
use Swoole\Coroutine\Channel;

class Privmsg implements HandlerInterface
{
    private $cli;
    private $eventBroadcastChannel;

    public function __construct(Client $cli, Channel $eventBroadcastChannel)
    {
        $this->cli = $cli;
        $this->eventBroadcastChannel = $eventBroadcastChannel;
    }

    public function handle(MessageObject $message_object): void
    {
        // In a PRIVMSG the options are our chat message
        $message = trim($message_object->options, ": \t\n\r\0\x0B"); // Adding colon to trim list to get it off the front. We've got to trim the newline off the end, so might as well
        $message_object->options = $message; // TODO: Properly Sanitize content

        if ($message[0] === "!") {
            // Bot Command, see if we have one to match
            $has_args = strpos($message, " ");
            if ($has_args !== false) {
                $command = substr($message, 1, $has_args - 1);
            } else {
                $command = substr($message, 1);
            }
            $command_class = "Bot\\Commands\\" . ucwords(strtolower($command) . "Command");
            if (class_exists($command_class)) {
                $cmd = new $command_class($this->cli, $this->eventBroadcastChannel);
                $cmd->run($message_object);
            }
        }

        if ($message_object->tags['user-id']) {
            $twitchApi = new TwitchApi();
            $message_object->user = $twitchApi->getUserById($message_object->tags['user-id']);
        }
        // Chat Message;Send to overlay
        $event = new EventObject('chat', $message_object);
        $this->eventBroadcastChannel->push($event);
    }
}
