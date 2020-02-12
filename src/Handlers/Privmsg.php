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
        //Adding colon to trim list to get it off the front. We've got to trim the newline off the end, so might as well
        $message = trim($message_object->options, ": \t\n\r\0\x0B");
        $message_object->options = $message; // TODO: Properly Sanitize content

        if ($message[0] === "!") {
            // Bot Command, see if we have one to match
            $command = $this->parseCommand($message);
            if ($this->botCommandExists($command)) {
                $this->runBotCommand($command, $message_object);
            }
        }

        // TODO: This doesn't belong here.  Move it.
        if (
            property_exists($message_object, 'tags')
            && is_array($message_object->tags)
            && array_key_exists('user-id', $message_object->tags)
        ) {
            $twitchApi = new TwitchApi();
            $message_object->user = $twitchApi->connect()->getUserById($message_object->tags['user-id']);
        }

        // Chat Message;Send to overlay
        $event = new EventObject('chat', $message_object);
        $this->eventBroadcastChannel->push($event);
    }

    public function parseCommand(string $message): string
    {
        $has_args = strpos($message, " ");
        if ($has_args !== false) {
            $command = substr($message, 1, $has_args - 1);
        } else {
            $command = substr($message, 1);
        }
        return $command;
    }

    public function botCommandClass(string $command): string
    {
        return "Bot\\Commands\\" . ucwords(strtolower($command) . "Command");
    }

    public function botCommandExists($command): bool
    {
        $commandClass = $this->botCommandClass($command);
        if (class_exists($commandClass)) {
            return true;
        }
        return false;
    }

    public function runBotCommand(string $command, MessageObject $messageObject)
    {
        $commandClass = $this->botCommandClass($command);
        $cmd = new $commandClass($this->cli, $this->eventBroadcastChannel);
        $cmd->run($messageObject);
    }
}
