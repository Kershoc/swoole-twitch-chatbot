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
        $this->parser = new MessageParser($this->dispatcher);
    }

    public function run()
    {
        while (true) {
            $data = $this->chatChannel->pop();
            if ($data) {
                $data = trim($data);
                if(substr_count($data, "\n") > 0) {
                    $data = explode("\n", $data);
                    foreach ($data as $item) {
                        $this->parser->parse($item);
                    }
                } else {
                    $this->parser->parse($data);
                }
            }
        }

    }

}