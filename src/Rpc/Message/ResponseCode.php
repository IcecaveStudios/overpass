<?php
namespace Icecave\Overpass\Rpc\Message;

use Eloquent\Enumeration\AbstractEnumeration;

class ResponseCode extends AbstractEnumeration
{
    const SUCCESS = 0;
    const EXCEPTION = 10;
    const INVALID_MESSAGE = 11;
    const UNKNOWN_PROCEDURE = 12;
    const INVALID_ARGUMENTS = 13;
}
