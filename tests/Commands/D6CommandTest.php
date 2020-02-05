<?php

namespace Commands;

use Bot\Commands\D6Command;
use Bot\MessageObject;
use Swoole\Coroutine\Http\Client;
use PHPUnit\Framework\TestCase;

class D6CommandTest extends TestCase
{
    private $command;
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->command = new D6Command($this->client);
    }

    public function testRun()
    {
        $msgObj = $this->createMock(MessageObject::class);
        $msgObj->irc_room = '#test_room';
        $msgObj->tags['display-name'] = 'test-name';

        $this->client->expects($this->once())
            ->method('push')
            ->with($this->stringContains("PRIVMSG #test_room :test-name has rolled a"));

        $this->command->run($msgObj);
    }
}
