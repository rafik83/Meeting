<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\InvalidReceiverException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;

interface SMSSenderInterface
{
    /**
     * @param SMS $sms
     *
     * @throws FailToSendSMSException
     * @throws InvalidReceiverException
     */
    public function send(SMS $sms);
}
