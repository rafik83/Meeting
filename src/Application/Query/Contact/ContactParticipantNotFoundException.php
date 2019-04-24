<?php

namespace Proximum\Vimeet\Application\Query\Contact;

class ContactParticipantNotFoundException extends \DomainException
{
    protected $message = 'Contact participant not found.';
}
