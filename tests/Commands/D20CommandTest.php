<?php

namespace Commands;

use Bot\Commands\D20Command;
use Bot\EventObject;
use Bot\MessageObject;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Http\Client;
use Swoole\Coroutine\Channel;

class D20CommandTest extends TestCase
{
    private $command;
    private $client;
    private $broadcaster;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->broadcaster = $this->createMock(Channel::class);
        $this->command = new D20Command($this->client, $this->broadcaster);
    }

    public function testRun()
    {
        $msgObj = $this->createMock(MessageObject::class);
        $msgObj->irc_room = '#test_room';
        $msgObj->tags['display-name'] = 'test-name';

        $this->client->expects($this->once())
            ->method('push')
            ->with($this->logicalOr(
                $this->stringContains("PRIVMSG #test_room :test-name has rolled a"),
                $this->stringContains("PRIVMSG #test_room :test-name CRITS! 20"),
                $this->stringContains("PRIVMSG #test_room :test-name rolls 1 Critical Fail!")
            ));

        $this->broadcaster->expects($this->once())
            ->method('push')
            ->with($this->logicalAnd(
                $this->isInstanceOf(EventObject::class),
                $this->objectHasAttribute('event'),
                $this->objectHasAttribute('payload')
            ));

        $this->command->run($msgObj);
    }
}
