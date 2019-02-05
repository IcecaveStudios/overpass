<?php

namespace Icecave\Overpass\Amqp;

interface DeclarationManagerInterface
{
    public function exchange();
    public function heartbeat();
}
