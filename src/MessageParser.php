<?php
/*
 * Parse the twitch chat message
 * See: http://www.hydrogen18.com/blog/parsing-twitch-chat-to-build-a-bot.html
 *
 * What I want this class to do is take the message and parse it into some sort of object
 * That object can then be passed off to some sort of queue to be consumed later.
 * Maybe Setup and inject a dispatcher.  Parse the line send to dispatcher.
 *
 * <tags> <user/host> COMMAND
 */
namespace Bot;

use Co\http\Client;

class MessageParser
{
    private $dispatcher;

    public function __construct($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function parse($data) :void
    {
        echo "[".date('Y-m-d H:i:s', time())."] " . $data . "\n";

        $tags = [];
        $irc_user = null;
        $irc_room = null;

        // Check if we have tags
        if ($data[0] === '@') {
            // Tags Payload
            $first_space = strpos($data, ' ');
            $rawtags = substr($data, 1, $first_space);
            $rawtags = explode(';', $rawtags);
            foreach ($rawtags as $tag) {
                $pair = explode('=',$tag);
                $tags[$pair[0]] = $pair[1];
            }
            // Remove tags from Payload
            $data = substr($data, ++$first_space);
        }

        // Either IRCUser or Command is next.  Check for colon;
        if ($data[0] === ':') {
            $first_space = strpos($data, ' ');
            $irc_user = substr($data, 0, $first_space);
            // Remove irc user from payload
            $data = substr($data, ++$first_space);
        }

        // At this point we should have a command at the beginning of our payload.
        $first_space = strpos($data, ' ');
        $command = substr($data, 0, $first_space);
        // Remove command from payload
        $data = substr($data, ++$first_space);

        // Next Comes Room
        if ($data[0] === '#') {
            $first_space = strpos($data, ' ');
            if ($first_space === false) {
                // No more spaces, just room is left;
                $irc_room = $data;
                $data = ''; // Empty the string
            } else {
                $irc_room = substr($data, 0, $first_space);
                // Remove irc room from payload
                $data = substr($data, ++$first_space);
            }
        }

        // What's left are the command options / message
        $command_options = $data;

        $message_object = new MessageObject($command, $command_options, $tags, $irc_user, $irc_room);

        $this->dispatcher->dispatch($message_object);

    }
}