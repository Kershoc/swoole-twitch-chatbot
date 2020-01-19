<?php

include('vendor/autoload.php');

use Symfony\Component\Dotenv\Dotenv;
use Bot\Server;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

new Server();
