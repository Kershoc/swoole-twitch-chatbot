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
        $this->server->on('request', [$this, 'onRequest']); // To support regular http requests as well.

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
        Coroutine::create([$this, 'timedCommandRunner']);
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
        $response->end();
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
        $listener = new ChatListener($this->chat_client->client, $this->ChatListenerChannel, $this->EventBroadcasterChannel);
        $listener->run();
    }

    public function timedCommandRunner()
    {
        // wait for chat client come up.  Better way to do this??
        while ( ! $this->chat_client->client instanceof Client) {
            \co::sleep(0.1);
        }
        foreach (new \DirectoryIterator('src/Commands/Timed/') as $item) {
            $class = 'Bot\\Commands\\Timed\\' . $item->getBasename('.php');
            if (class_exists($class)) {
                $timedCommand = new $class($this->chat_client->client);
                $this->server->tick($timedCommand->repeatAfter, [$timedCommand, 'run']);
                echo "[".date("Y-m-d H:i:s")."] {$class} Timer Started! {$timedCommand->repeatAfter}ms interval \n";
            }
        }
    }

}
