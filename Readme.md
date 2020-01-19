# swoole-twitch-bot

Quick POC to try using Swoole to create a twitch chatbot.

Sets up a websocket server on port localhost:1337

This server will broadcast events that can be used to drive an overlay page  

Sets up a websocket connection to the twitch irc chat
This client will listen to chat and can respond to configured commands.
It can also broadcast events to the overlay driver. 

#### Requirements

* php 7.1+ CLI 
* Swoole 4

#### Install Swoole
```bash
pecl install swoole
```

#### Usage
```bash
php chatbot.php
```

#### ToDo
* Overlays - Right now the overlay page is a console.log debug stub
* Write a decent readme
* Abstract away the PoC coupled systems marked with TODOs