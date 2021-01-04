<?php

namespace Proximum\Vimeet\Application\Adapter;

use Proximum\Vimeet\Application\Exception\Messaging\CampaignSendingFailedException;
use Proximum\Vimeet\Domain\Model\Messaging\Message;

interface EmailingSenderInterface
{
    /**
     * Sends an emailing message to a given list of receivers.
     *
     * @param Message $message   The message to send
     * @param array   $receivers An array of ReceiverView instances indexed by email
     *
     * @throws CampaignSendingFailedException
     */
    public function send(Message $message, array $receivers);
}
