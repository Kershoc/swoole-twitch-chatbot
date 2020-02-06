<?php

namespace Commands\Timed;

use Bot\Commands\Timed\WarframeAnomaly;
use Bot\EventObject;
use PHPUnit\Framework\TestCase;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine\Http\Client;
use Symfony\Component\Dotenv\Dotenv;

class WarframeAnomalyIsUp extends WarframeAnomaly
{
    public function getWorldState()
    {
        $fakeWorldState = ['Tmp' => json_encode(['sfn' => 505])];
        return $fakeWorldState;
    }
}

class WarframeAnomalyIsDown extends WarframeAnomaly
{
    public function getWorldState()
    {
        $fakeWorldState = ['Tmp' => '"[]"'];
        return $fakeWorldState;
    }
}

class WarframeAnomalyTest extends TestCase
{
    private $command;
    private $client;
    private $broadcaster;

    public function __construct()
    {
        parent::__construct();
        $this->setOutputCallback(function () {
        });
        $env = new Dotenv();
        $env->load(__DIR__ . '/../../../.env.testing');
    }

    public function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->broadcaster = $this->createMock(Channel::class);
    }

    public function tearDown(): void
    {
        $this->command = null;
    }

    public function testRunIsUp(): void
    {
        $this->command = new WarframeAnomalyIsUp($this->client, $this->broadcaster);
        $this->client->expects($this->once())
            ->method('push')
            ->with($this->stringContains("PRIVMSG {$_ENV['TWITCH_ROOM']} :DANGER Will Tennoson! DANGER! Sentient Anomaly spotted in Ruse War Field!  Dispatch all available Railjack's to Investigate!"));

        $this->broadcaster->expects($this->once())
            ->method('push')
            ->with($this->logicalAnd(
                $this->isInstanceOf(EventObject::class),
                $this->objectHasAttribute('event'),
                $this->objectHasAttribute('payload')
            ));

        $this->command->run();
    }

    public function testRunIsDown(): void
    {
        $this->command = new WarframeAnomalyIsDown($this->client, $this->broadcaster);
        $this->client->expects($this->never())
            ->method('push');

        $this->broadcaster->expects($this->once())
            ->method('push')
            ->with($this->logicalAnd(
                $this->isInstanceOf(EventObject::class),
                $this->objectHasAttribute('event'),
                $this->objectHasAttribute('payload')
            ));

        $this->command->run();
    }
}
