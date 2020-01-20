<?php
/*
 * Probably better ways to do this.
 * But right now we making this our main class.
 * It will be the main EventDispatcher that we will use to drive our overlays ect.
 * A browser will connect over websockets and listen for events that we broadcast here.
 * That front end can then handle rendering the event if it wishes.
 */

namespace Bot;

use Bot\Clients\TwitchIrcWs;
use Swoole\Coroutine\http\Client;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine;
use Swoole\Websocket\Server as wsServer;

class Server
{
    public $server;
    public $chat_client;
    public $EventBroadcasterChannel;
    public $ChatListenerChannel;

    public function __construct()
    {
        $this->server = new wsServer("127.0.0.1", 1337);
        $this->server->set(array(
            'worker_num' => 1, // The number of worker processes
            'daemonize' => false, // Whether start as a daemon process
            'backlog' => 128, // TCP backlog connection number
            'document_root' => 'public_html', // Static Documents for regular http requests
            'enable_static_handler' => true, // Let Swoole handle static files.
        ));

        $this->server->on('start', [$this, 'onStart'] );
        $this->server->on('workerStart', [$this, 'onWorkerStart']);
        $this->server->on('message', [$this, 'onMessage']);
        $this->server->on('open', [$this, 'onOpen']);
        $this->server->on('request', [$this, 'onRequest']); // To support reqular http requests as well.

        $this->server->start();
    }

    public function onStart()
    {
    }

    public function onWorkerStart()
    {
        $this->EventBroadcasterChannel = new Channel();
        $this->ChatListenerChannel = new Channel();
        $this->chat_client = new TwitchIrcWs($this->ChatListenerChannel);
        Coroutine::create([$this, 'chatEventBroadcaster']);
        Coroutine::create([$this, 'chatClientListener']);
    }

    public function onOpen(wsServer $svr, $request)
    {
        echo "New Connection \n";
        $svr->push($request->fd, "Welcome to the Party!\n");
    }

    public function onMessage(wsServer $svr, $frame)
    {
        $svr->push($frame->fd, "Sup!\n");
    }

    public function onRequest($request, $response)
    {
        if ($request->server['request_uri'] === '/') {
            $response->sendfile('public_html/index.html');
            return;
        }
        // Blanket 404.  Using swooles default static handler to handle static files
        $response->status(404);
    }

    public function chatEventBroadcaster()
    {
        // TODO: This should be it own class and more robust
        while (true) {
            $data = $this->EventBroadcasterChannel->pop();
            if ($data) {
                foreach ($this->server->connections as $fd) {
                    if ($this->server->isEstablished($fd)) {
                        $this->server->push($fd, json_encode($data));
                    }
                }
            }
        }
    }

    public function chatClientListener()
    {
        // wait for chat client come up.  Better way to do this??
        while ( ! $this->chat_client->client instanceof Client) {
            \co::sleep(0.1);
        }

        // TODO: abstract some sort of TimedCommand system and put it someplace better then here
        $cli = $this->chat_client->client;
        $warframeRailjackAnomolyTimer = $this->server->tick(60000, function () use ($cli) {
            $warframeWorldState = new Client('content.warframe.com', 80);
            $warframeWorldState->get('/dynamic/worldState.php');
            $warframeWorldStateData = json_decode($warframeWorldState->getBody(),true);
            $warframeWorldState->close();
            $anomaly = json_decode($warframeWorldStateData['Tmp'], true);
            echo "[" . date('Y-m-d H:i:s') . "] Scanning Veil Proxima ... ".var_export(array_key_exists('sfn',$anomaly), true)."\n";
            if (!empty($anomaly) && array_key_exists('sfn', $anomaly)) {
                $cli->push("PRIVMSG {$_ENV['TWITCH_ROOM']} :DANGER Will Tennoson! DANGER! Sentient Anomaly spotted in Veil Proxima!  Dispatch all available Railjack's to Investigate!");
            }
        });

        // TODO: This should be its own class and more robust
        $dispatcher = new MessageDispatcher($this->chat_client->client, $this->EventBroadcasterChannel);
        $message_parser = new MessageParser($dispatcher);
        while (true) {
            $data = $this->ChatListenerChannel->pop();
            if ($data) {
                $data = trim($data);
                if(substr_count($data, "\n") > 0) {
                    $data = explode("\n", $data);
                    foreach ($data as $item) {
                        $message_parser->parse($item);
                    }
                } else {
                    $message_parser->parse($data);
                }
            }
        }
    }

}