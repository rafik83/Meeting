<?php


namespace Proximum\Vimeet\Application\Query\Networking;


class ClosedNetworkingException extends \RuntimeException
{
    public $message = 'The networking is close';
}
