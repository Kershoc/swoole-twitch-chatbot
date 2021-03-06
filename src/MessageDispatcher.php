<?php

/*
 * Start of message dispatcher.  Takes a parsed message object.
 * Determines what to do with it based on Command Received.
 * Right now I'm passing responses directly back.
 * Ideally I want to create a bunch of handlers for the commands
 * Then just dispatch the message off to the handler and let it do what it do.
 */
namespace Bot;

use Swoole\Coroutine\http\Client;
use Swoole\Coroutine\Channel;

class MessageDispatcher
{
    private $cli;
    private $channel;

    public function __construct(Client $cli, Channel $channel)
    {
        $this->cli = $cli;
        $this->channel = $channel;
    }

    public function dispatch(MessageObject $message_object): void
    {
        $command_class = "Bot\\Handlers\\" . ucwords(strtolower($message_object->command));
        if (class_exists($command_class)) {
            $handler = new $command_class($this->cli, $this->channel);
            $handler->handle($message_object);
        }
    }
}
