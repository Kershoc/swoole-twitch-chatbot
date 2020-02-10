<?php

namespace Bot;

use Swoole\Coroutine\http\Client;
use Swoole\Coroutine\Channel;

class ChatListener
{
    private $chatClient;
    private $eventChannel;
    private $chatChannel;
    private $dispatcher;
    private $parser;

    public function __construct(Client $cli, Channel $listenChannel, Channel $broadcastChannel)
    {
        $this->chatClient = $cli;
        $this->eventChannel = $broadcastChannel;
        $this->chatChannel = $listenChannel;
        $this->dispatcher = new MessageDispatcher($this->chatClient, $this->eventChannel);
        $this->parser = new MessageParser();
    }

    public function run()
    {
        while (true) {
            $data = $this->chatChannel->pop();
            if ($data) {
                $data = trim($data);
                if (substr_count($data, "\n") > 0) {
                    $data = explode("\n", $data);
                    foreach ($data as $item) {
                        $msgObj = $this->parser->parse($item);
                        $this->dispatcher->dispatch($msgObj);
                    }
                } else {
                    $msgObj = $this->parser->parse($data);
                    $this->dispatcher->dispatch($msgObj);
                }
            }
        }
    }
}
