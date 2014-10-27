<?php
require __DIR__ . '/../vendor/autoload.php';

Eloquent\Asplode\Asplode::install();

$logger = new Icecave\Stump\Logger();

$amqpConnection = new PhpAmqpLib\Connection\AMQPStreamConnection(
    'localhost',
    5672,
    'guest',
    'guest',
    '/'
);

$amqpChannel = $amqpConnection->channel();
