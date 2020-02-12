<?php

/*
 * Probably better ways to do this.
 * But right now we making this our main class.
 * It will be the main EventDispatcher that we will use to drive our overlays ect.
 * A browser will connect over websockets and listen for events that we broadcast here.
 * That front end can then handle rendering the event if it wishes.
 */

namespace Bot;

use Bot\Server\ChatEventBroadcaster;
use Bot\Server\TimedCommandRunner;
use Bot\Server\ChatListener;
use Bot\Clients\TwitchIrcWs;
use Swoole\Coroutine\Channel;
use Swoole\Coroutine;
use Swoole\Websocket\Server as wsServer;

class Server
{
    public $server;
    public $chatClient;
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

        $this->server->on('workerStart', [$this, 'onWorkerStart']);
        $this->server->on('message', [$this, 'onMessage']);
        $this->server->on('open', [$this, 'onOpen']);
        $this->server->on('request', [$this, 'onRequest']); // To support regular http requests as well.
    }

    public function onWorkerStart()
    {
        $this->EventBroadcasterChannel = new Channel();
        $this->ChatListenerChannel = new Channel();
        $chatClient = new TwitchIrcWs($this->ChatListenerChannel);
        $this->chatClient = $chatClient->start();
        $chatClient->login();
        $chatEventBroadcaster = new ChatEventBroadcaster(
            $this->server,
            $this->EventBroadcasterChannel
        );
        $chatClientListener = new ChatListener(
            $this->chatClient,
            $this->ChatListenerChannel,
            $this->EventBroadcasterChannel
        );
        $timedCommandRunner = new TimedCommandRunner(
            $this->server,
            $this->chatClient,
            $this->EventBroadcasterChannel
        );
        Coroutine::create([$chatEventBroadcaster, 'run']);
        Coroutine::create([$chatClientListener, 'run']);
        Coroutine::create([$timedCommandRunner, 'run']);
    }

    public function onOpen(wsServer $svr, $request)
    {
        $svr->push($request->fd, '{"msg": "Greetings Starfighter! You have been recruited by the Star League 
to defend the frontier against Xur and the Ko-Dan armada."}');
    }

    public function onMessage(wsServer $svr, $frame)
    {
    }

    public function onRequest($request, $response)
    {
        if ($request->server['request_uri'] === '/') {
            $response->sendfile('public_html/index.html');
            return;
        }
        // Blanket 404.  Using swooles default static handler to handle static files
        $response->status(404);
        $response->end();
    }
}
