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

class MessageParser
{
    private $tags = [];
    private $ircUser = null;
    private $ircCommand;
    private $ircRoom = null;

    public function parse($data): MessageObject
    {
        echo "[" . date('Y-m-d H:i:s', time()) . "] " . $data . "\n";

        // Check if we have tags
        if ('@' === substr($data, 0, 1)) {
            $data = $this->parseTags($data);
        }

        // Either IRCUser or Command is next.  Check for colon;
        if (':' === substr($data, 0, 1)) {
            $data = $this->parseIrcUser($data);
        }

        // At this point we should have a command at the beginning of our payload.
        $data = $this->parseIrcCommand($data);

        // Next Comes Room
        if ('#' === substr($data, 0, 1)) {
            $data = $this->parseIrcRoom($data);
        }

        // What's left are the command options / message
        $command_options = $data;

        return new MessageObject($this->ircCommand, $command_options, $this->tags, $this->ircUser, $this->ircRoom);
    }

    private function parseTags(string $message): string
    {
        // Tags Payload
        $first_space = strpos($message, ' ');
        $rawTags = substr($message, 1, $first_space - 1);
        $rawTags = explode(';', $rawTags);
        foreach ($rawTags as $tag) {
            $pair = explode('=', $tag);
            $this->tags[$pair[0]] = $pair[1];
        }
        // Remove tags from Payload
        return substr($message, ++$first_space);
    }

    private function parseIrcUser(string $message): string
    {
        $first_space = strpos($message, ' ');
        $this->ircUser = substr($message, 0, $first_space);
        // Remove irc user from payload
        return substr($message, ++$first_space);
    }

    private function parseIrcCommand(string $message): string
    {
        $first_space = strpos($message, ' ');
        $this->ircCommand = substr($message, 0, $first_space);
        // Remove command from payload
        return substr($message, ++$first_space);
    }

    private function parseIrcRoom(string $message): string
    {
        $first_space = strpos($message, ' ');
        if ($first_space === false) {
            // No more spaces, just room is left;
            $this->ircRoom = $message;
            return '';
        } else {
            $this->ircRoom = substr($message, 0, $first_space);
            // Remove irc room from payload
            return substr($message, ++$first_space);
        }
    }
}
