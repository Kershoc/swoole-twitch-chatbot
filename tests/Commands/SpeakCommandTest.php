<?php

namespace Commands;

use Bot\Commands\SpeakCommand;
use Bot\MessageObject;
use Swoole\Coroutine\http\Client;
use PHPUnit\Framework\TestCase;

class SpeakCommandTest extends TestCase
{
    private $command;
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->command = new SpeakCommand($this->client);
    }

    public function testRun(): void
    {
        $msgObj = $this->createMock(MessageObject::class);
        $msgObj->irc_room = '#test_room';

        $this->client->expects($this->once())
            ->method('push')
            ->with($this->stringContains("PRIVMSG #test_room :Woof! Woof!"));

        $this->command->run($msgObj);
    }
}
