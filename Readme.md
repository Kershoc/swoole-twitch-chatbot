# swoole-twitch-bot

Quick proof of concept to try using Swoole to create a twitch chatbot.

Sets up a websocket server on port localhost:1337

This server will broadcast events that can be used to drive an overlay page  

Sets up a websocket connection to the twitch irc chat
This client will listen to chat and can respond to configured commands.
It can also broadcast events to the overlay driver. 

#### Requirements

* php 7.2+ CLI 
* Swoole 4

#### Install Swoole
```bash
pecl install swoole
```
#### Install Dependencies
```bash
composer install
```
#### Configure
Copy .env-default to .env and fill in the fields.  
```
TWITCH_OAUTH_PASS=oauth:...
TWITCH_CLIENT_ID=...
TWITCH_NICK=UserDisplayName
TWITCH_ROOM="#room"
```
If you are interested in running the tests, you will want to also make a .env.testing file with dummy data inside.

#### Usage
```bash
php chatbot.php
```

#### ToDo 
* Write a decent readme
* Abstract away the PoC coupled systems marked with TODOs
* Convert Vuejs PoC to a full Vuejs app
* Fill in missing message parsing for irc chat
 