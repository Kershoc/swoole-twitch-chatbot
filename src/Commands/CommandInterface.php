<?php

namespace Bot\Commands;

use Bot\MessageObject;

interface CommandInterface
{
    public function run(MessageObject $message_object): void;
}