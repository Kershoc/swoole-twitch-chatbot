<?php

namespace Commands;

use Bot\Commands\ListCommand;
use Bot\MessageObject;
use Swoole\Coroutine\Http\Client;
use PHPUnit\Framework\TestCase;

class ListCommandTest extends TestCase
{
    private $command;
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->command = new ListCommand($this->client);
    }

    public function testRun()
    {
        $msgObj = $this->createMock(MessageObject::class);
        $msgObj->irc_room = '#test_room';

        $this->client->expects($this->once())
            ->method('push')
            ->with($this->stringContains("PRIVMSG #test_room :Available commands are !speak and !dice"));

        $this->command->run($msgObj);
    }
}
