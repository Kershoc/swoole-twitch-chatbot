<?php

namespace Commands;

use Bot\Commands\D20Command;
use Bot\EventObject;
use Bot\MessageObject;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Http\Client;
use Swoole\Coroutine\Channel;

class D20CommandNonRandom extends D20Command
{
    public function rollDie()
    {
        return 10;
    }
}

class D20CommandNat1 extends D20Command
{
    public function rollDie()
    {
        return 1;
    }
}

class D20CommandNat20 extends D20Command
{
    public function rollDie()
    {
        return 20;
    }
}

class D20CommandTest extends TestCase
{
    private $command;
    private $client;
    private $broadcaster;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->broadcaster = $this->createMock(Channel::class);
    }

    protected function tearDown(): void
    {
        $this->command = null;
    }

    public function testRun()
    {
        $this->command = new D20CommandNonRandom($this->client, $this->broadcaster);
        $msgObj = $this->createMock(MessageObject::class);
        $msgObj->irc_room = '#test_room';
        $msgObj->tags['display-name'] = 'test-name';

        $this->client->expects($this->once())
            ->method('push')
            ->with($this->stringContains("PRIVMSG #test_room :test-name has rolled a"));

        $this->broadcaster->expects($this->once())
            ->method('push')
            ->with($this->logicalAnd(
                $this->isInstanceOf(EventObject::class),
                $this->objectHasAttribute('event'),
                $this->objectHasAttribute('payload')
            ));

        $this->command->run($msgObj);
    }

    public function testRunNat1()
    {
        $this->command = new D20CommandNat1($this->client, $this->broadcaster);
        $msgObj = $this->createMock(MessageObject::class);
        $msgObj->irc_room = '#test_room';
        $msgObj->tags['display-name'] = 'test-name';

        $this->client->expects($this->once())
            ->method('push')
            ->with($this->stringContains("PRIVMSG #test_room :test-name rolls 1 Critical Fail!"));

        $this->broadcaster->expects($this->once())
            ->method('push')
            ->with($this->logicalAnd(
                $this->isInstanceOf(EventObject::class),
                $this->objectHasAttribute('event'),
                $this->objectHasAttribute('payload')
            ));

        $this->command->run($msgObj);
    }

    public function testRunNat20()
    {
        $this->command = new D20CommandNat20($this->client, $this->broadcaster);
        $msgObj = $this->createMock(MessageObject::class);
        $msgObj->irc_room = '#test_room';
        $msgObj->tags['display-name'] = 'test-name';

        $this->client->expects($this->once())
            ->method('push')
            ->with($this->stringContains("PRIVMSG #test_room :test-name CRITS! 20"));

        $this->broadcaster->expects($this->once())
            ->method('push')
            ->with($this->logicalAnd(
                $this->isInstanceOf(EventObject::class),
                $this->objectHasAttribute('event'),
                $this->objectHasAttribute('payload')
            ));

        $this->command->run($msgObj);
    }

    public function testRollDie()
    {
        $this->command = new D20Command($this->client, $this->broadcaster);
        $result = $this->command->rollDie();

        $this->assertIsInt($result);

        $this->assertThat(
            $result,
            $this->logicalAnd(
                $this->greaterThan(0),
                $this->lessThan(21)
            )
        );
    }
}
