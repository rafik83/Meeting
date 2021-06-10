<?php


namespace Proximum\Vimeet\Application\Query\Networking;


class NetworkingNotAccessibleException extends \RuntimeException
{
    public $message = 'The networking is close or sheet has no access to networking';
}
