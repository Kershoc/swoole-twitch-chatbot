<?php

namespace Bot\Commands\Timed;

use Bot\EventObject;
use Swoole\Coroutine\http\Client;
use Swoole\Coroutine\Channel;

class WarframeAnomaly implements TimedCommandInterface
{
    public $repeatAfter = 60000;
    private $client;
    private $broadcaster;
    private $zones = [
        505 => 'Ruse War Field',
        510 => 'Gian Point',
        550 => 'Nsu Grid',
        551 => "Ganalen's Grave",
        552 => 'Rya',
        553 => 'Flexa',
        554 => 'H-2 Cloud',
        555 => 'R-9 Cloud',
    ];

    public function __construct(Client $cli, Channel $broadcaster)
    {
        $this->client = $cli;
        $this->broadcaster = $broadcaster;
    }

    public function run(): void
    {
        $warframeWorldStateData = $this->getWorldState();

        $anomaly = json_decode($warframeWorldStateData['Tmp'], true);

        $isUp = false;
        if (is_array($anomaly) && array_key_exists('sfn', $anomaly)) {
            $isUp = $this->zones[$anomaly['sfn']];
            $this->client->push("PRIVMSG {$_ENV['TWITCH_ROOM']} :DANGER Will Tennoson! DANGER! Sentient Anomaly "
             . "spotted in {$isUp}!  Dispatch all available Railjack's to Investigate!");
        }
        echo "[" . date('Y-m-d H:i:s') . "] Scanning Veil Proxima ... " . var_export($isUp, true) . "\n";
        $payload = [
            'command' => 'WarframeAnomaly',
            'status' => $isUp
        ];
        $event = new EventObject('popup', $payload);
        $this->broadcaster->push($event);
    }

    /**
     * @codeCoverageIgnore
     */
    public function getWorldState()
    {
        $warframeWorldState = new Client('content.warframe.com', 80);
        $warframeWorldState->get('/dynamic/worldState.php');
        $warframeWorldStateData = json_decode($warframeWorldState->getBody(), true);
        $warframeWorldState->close();
        return $warframeWorldStateData;
    }
}
