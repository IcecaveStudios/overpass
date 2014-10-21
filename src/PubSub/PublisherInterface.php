<?php
namespace Icecave\Overpass\PubSub;

interface PublisherInterface
{
    /**
     * Publish a message.
     *
     * @param string $topic   The topic that the payload is published to.
     * @param mixed  $payload The payload to publish.
     */
    public function publish($topic, $payload);
}
