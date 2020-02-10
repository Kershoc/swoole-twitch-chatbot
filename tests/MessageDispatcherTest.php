<?php

namespace Bot;

use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Http\Client;

class MessageDispatcherTest extends TestCase
{
    public $dispatcher;

    public function setUp(): void
    {
        $client = $this->createMock(Client::class);
        $channel = $this->createMock(Channel::class);
        $this->dispatcher = new MessageDispatcher($client, $channel);
    }

    public function tearDown(): void
    {
        $this->dispatcher = null;
    }

    public function testDispatch(): void
    {
        $msgObj = $this->createMock(MessageObject::class);
        $msgObj->command = 'TEST';

        $this->dispatcher->dispatch($msgObj);
    }
}

namespace Bot\Handlers;

use Bot\MessageObject;
use PHPUnit\Framework\TestCase;

class Test implements HandlerInterface
{
    public function handle(MessageObject $msgObj): void
    {
        TestCase::assertTrue(true);
    }
}
