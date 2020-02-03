<?php

/*
 * Generic for now.
 * TODO: Validation of content; special getters; immutable;
 */
namespace Bot;

class MessageObject
{

    public $command;
    public $tags = [];
    public $irc_user = null;
    public $irc_room = null;
    public $options;

    public function __construct($command, $options, $tags = [], $irc_user = null, $irc_room = null)
    {
        $this->command = $command;
        $this->options = $options;
        $this->tags = $tags;
        $this->irc_user = $irc_user;
        $this->irc_room = $irc_room;
    }
}
