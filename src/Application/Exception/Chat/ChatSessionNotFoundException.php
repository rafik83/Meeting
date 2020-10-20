<?php

namespace Proximum\Vimeet\Application\Exception\Chat;

class ChatSessionNotFoundException extends \Exception
{
    public $message = 'Chat session not found';
}
