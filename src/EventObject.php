<?php

namespace Bot;

class EventObject
{
    public $event;
    public $payload;

    //TODO: Need to really think this part and design it out; basic stub for now

    public function __construct(string $event, $payload)
    {
        $this->event = $event;
        $this->payload = $payload;
    }

}