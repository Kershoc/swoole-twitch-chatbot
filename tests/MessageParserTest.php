<?php

namespace Bot;

use Bot\MessageParser;
use PHPUnit\Framework\TestCase;

class MessageParserTest extends TestCase
{
    private $parser;

    public function setUp(): void
    {
        $this->parser = new MessageParser();
    }

    public function tearDown(): void
    {
        $this->parser = null;
    }

    public function testParseRoomStateMessage(): void
    {
        $msg = '@emote-only=0;followers-only=-1;room-id=111111 :tmi.twitch.tv ROOMSTATE #test-room';
        $result = $this->parser->parse($msg);

        $this->isInstanceOf(MessageObject::class, $result);
        $this->assertEquals($result->tags, [
            'emote-only' => '0',
            'followers-only'  => '-1',
            'room-id' => '111111'
        ]);
        $this->assertEquals($result->options, '');
        $this->assertEquals($result->irc_user, ':tmi.twitch.tv');
        $this->assertEquals($result->command, 'ROOMSTATE');
        $this->assertEquals($result->irc_room, '#test-room');
    }

    public function testParsePingMessage(): void
    {
        $msg = 'PING :tmi.twitch.tv';
        $result = $this->parser->parse($msg);
        $this->isInstanceOf(MessageObject::class, $result);
        $this->assertEquals($result->command, 'PING');
    }

    public function testParsePrivmsgMessage(): void
    {
        $msg = "@display-name=User;user-id=111111 :<user>!<user>@<user>.tmi.twitch.tv PRIVMSG #test-room :Hello";
        $result = $this->parser->parse($msg);

        $this->isInstanceOf(MessageObject::class, $result);
        $this->assertEquals($result->tags, [
            'display-name' => 'User',
            'user-id' => '111111'
        ]);
        $this->assertEquals($result->options, ':Hello');
        $this->assertEquals($result->irc_user, ':<user>!<user>@<user>.tmi.twitch.tv');
        $this->assertEquals($result->command, 'PRIVMSG');
        $this->assertEquals($result->irc_room, '#test-room');
    }

    public function testParsePrivmsgMessageWithoutTags(): void
    {
        $msg = ":<user>!<user>@<user>.tmi.twitch.tv PRIVMSG #test-room :Hello";
        $result = $this->parser->parse($msg);

        $this->isInstanceOf(MessageObject::class, $result);
        $this->assertEquals($result->tags, []);
        $this->assertEquals($result->options, ':Hello');
        $this->assertEquals($result->irc_user, ':<user>!<user>@<user>.tmi.twitch.tv');
        $this->assertEquals($result->command, 'PRIVMSG');
        $this->assertEquals($result->irc_room, '#test-room');
    }
}
