<?php

namespace Handlers;

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
}
