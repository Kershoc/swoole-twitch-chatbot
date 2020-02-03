<?php

namespace Bot\Handlers;

use Bot\MessageObject;

interface HandlerInterface
{
    public function handle(MessageObject $messageObject): void;
}
