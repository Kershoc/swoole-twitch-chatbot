<?php

namespace Commands;

use Bot\Commands\DiceCommand;
use Bot\MessageObject;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Http\Client;

class DiceCommandTest extends TestCase
{
    private $command;
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->command = new DiceCommand($this->client);
    }

    public function testRun()
    {
        $msgObj = $this->createMock(MessageObject::class);
        $msgObj->irc_room = '#test_room';

        $this->client->expects($this->once())
            ->method('push')
            ->with($this->stringContains("PRIVMSG #test_room :You can roll a !d20 or a !d6"));

        $this->command->run($msgObj);
    }
}
