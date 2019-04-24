<?php

namespace Proximum\Vimeet\Application\Query\Badge\ScannedUserEventProfile;

class UserNotFoundException extends \DomainException
{
    protected $message = 'User not found';
}
