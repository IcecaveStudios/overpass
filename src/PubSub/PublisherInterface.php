<?php
namespace Icecave\Overpass\PubSub;

use Psr\Log\LoggerAwareInterface;

interface PublisherInterface extends LoggerAwareInterface
{
    /**
     * Publish a message.
     *
     * @param string $topic   The topic that the payload is published to.
     * @param mixed  $payload The payload to publish.
     */
    public function publish($topic, $payload);
}
