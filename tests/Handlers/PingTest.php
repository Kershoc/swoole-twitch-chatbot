<?php

namespace Handlers;

use Bot\Handlers\Ping;
use Bot\MessageObject;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Http\Client;

class PingTest extends TestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
    }

    public function testHandle(): void
    {
        $this->setOutputCallback(function () {
        });

        $ping = new Ping($this->client);
        $msgObj = $this->createMock(MessageObject::class);

        $this->client->expects($this->once())
            ->method('push')
            ->with($this->stringContains("PONG :tmi.twitch.tv"));

        $ping->handle($msgObj);
    }
}
