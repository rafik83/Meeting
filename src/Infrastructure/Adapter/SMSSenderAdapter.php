<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use GuzzleHttp\Exception\ClientException;
use Ovh\Api;
use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\NoServiceAvailableException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\NoSenderAvailableException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;

class SMSSenderAdapter implements SMSSenderInterface
{
    const SMS_SENDER = 'PROXIMUM';

    /** @var Api */
    private $api;

    /**
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @param SMS $sms
     *
     * @throws FailToSendSMSException
     */
    public function send(SMS $sms)
    {
        $service = $this->getService();
        $sender  = $this->getSenderName($service);

        try {
            $response = $this->api->post(
                sprintf('/sms/%s/jobs', $service),
                [
                    'message'   => $sms->getMessage(),
                    'receivers' => [$sms->getReceiver()],
                    'sender'    => $sender,
                ]
            );

            if (!empty($response['invalidReceivers'])) {
                throw new FailToSendSMSException(
                    sprintf(
                        'The SMS could not be sent to this user %s as it is not a valid international phone number',
                        implode(', ', $response['invalidReceivers'])
                    )
                );
            }
        } catch (ClientException $exception) {
            throw new FailToSendSMSException($exception->getMessage());
        }
    }

    /**
     * @return string
     *
     * @throws NoServiceAvailableException
     */
    private function getService()
    {
        $services = $this->api->get('/sms');

        $service = reset($services);

        if ($service === false) {
            throw new NoServiceAvailableException('No service available to send SMS');
        }

        return $service;
    }

    /**
     * @param string $service
     *
     * @return string
     *
     * @throws NoSenderAvailableException
     */
    private function getSenderName($service)
    {
        $senders = $this->api->get(sprintf('/sms/%s/senders', $service));
        $sender  = reset($senders);

        if ($sender === false|| !in_array(self::SMS_SENDER, $senders)) {
            throw new NoSenderAvailableException('No sender available to send SMS');
        }

        return self::SMS_SENDER;
    }
}
