<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\SMS;

use Proximum\Vimeet\Domain\Messaging\SMS\SMS;

interface SMSProviderInterface
{
    public function sendMessage(SMS $sms): void;

    public function canSend(SMS $sms): bool;
}
