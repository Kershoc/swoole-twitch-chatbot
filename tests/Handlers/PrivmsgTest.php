<?php

namespace Handlers;

use Bot\EventObject;
use Bot\Handlers\Privmsg;
use Bot\MessageObject;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Http\Client;

class PrivmsgTest extends TestCase
{
    private $client;
    private $broadcaster;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->broadcaster = $this->createMock(Channel::class);
    }

    public function testParseCommandWithoutArgs(): void
    {
        $privmsg = new Privmsg($this->client, $this->broadcaster);
        $result = $privmsg->parseCommand("!command");
        $this->assertEquals($result, "command");
    }

    public function testParseCommandWithArgs(): void
    {
        $privmsg = new Privmsg($this->client, $this->broadcaster);
        $result = $privmsg->parseCommand("!command args");
        $this->assertEquals($result, "command");
    }

    public function testBotCommandClass(): void
    {
        $privmsg = new Privmsg($this->client, $this->broadcaster);
        $result = $privmsg->botCommandClass('test');
        $this->assertEquals("Bot\\Commands\\TestCommand", $result);

        $result = $privmsg->botCommandClass('TeSt');
        $this->assertEquals("Bot\\Commands\\TestCommand", $result);

        $result = $privmsg->botCommandClass('TEST');
        $this->assertEquals("Bot\\Commands\\TestCommand", $result);
    }

    public function testBotCommandExists(): void
    {
        $privmsg = new Privmsg($this->client, $this->broadcaster);
        $result = $privmsg->botCommandExists('test');
        $this->assertTrue($result);

        $result = $privmsg->botCommandExists('superdummytestcommandthatshouldntpossiblyexist');
        $this->assertFalse($result);
    }

    public function testRunBotCommand(): void
    {
        $msgObj = $this->createMock(MessageObject::class);
        $privmsg = new Privmsg($this->client, $this->broadcaster);
        $privmsg->runBotCommand('test', $msgObj);
    }

    public function testHandle()
    {
        $msgObj = $this->createMock(MessageObject::class);
        $msgObj->options = ":!test";
        $privmsg = new Privmsg($this->client, $this->broadcaster);

        $this->broadcaster->expects($this->once())
            ->method('push')
            ->with($this->logicalAnd(
                $this->isInstanceOf(EventObject::class),
                $this->objectHasAttribute('event'),
                $this->objectHasAttribute('payload')
            ));
        $privmsg->handle($msgObj);
    }
}

namespace Bot\Commands;

use Bot\MessageObject;
use PHPUnit\Framework\TestCase;

class TestCommand implements CommandInterface
{
    public function run(MessageObject $msgObj): void
    {
        TestCase::assertTrue(true);
    }
}
