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
use GuzzleHttp\Exception\ServerException;
use Ovh\Api;
use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\InvalidReceiverException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\TwilioClient;

class SMSSenderAdapter implements SMSSenderInterface
{
    /** @var Api */
    private $api;

    /** @var string */
    private $ovhServiceName;

    /** @var string */
    private $ovhSenderName;

    /** @var TwilioClient */
    private $twilioClient;

    /**
     * @param Api          $api
     * @param string       $ovhServiceName
     * @param string       $ovhSenderName
     * @param TwilioClient $twilioClient
     */
    public function __construct(
        Api $api,
        $ovhServiceName,
        $ovhSenderName,
        TwilioClient $twilioClient
    ) {
        $this->api = $api;
        $this->ovhServiceName = $ovhServiceName;
        $this->ovhSenderName = $ovhSenderName;
        $this->twilioClient = $twilioClient;
    }

    /**
     * {@inheritdoc}
     */
    public function send(SMS $sms)
    {
        try {

            if (mb_strpos($sms->getReceiver(), '+1') === 0) {
                $this->twilioClient->sendMessage($sms->getReceiver(), $sms->getMessage());

                return;
            }

            $content = [
                'message'   => $sms->getMessage(),
                'receivers' => [$sms->getReceiver()],
                'sender'    => $this->ovhSenderName,
            ];

            if (false === $sms->hasStopClause()) {
                $content['noStopClause'] = true;
            }

            $response = $this->api->post(
                sprintf('/sms/%s/jobs', $this->ovhServiceName),
                $content
            );

            if (!empty($response['invalidReceivers'])) {
                throw new InvalidReceiverException(
                    sprintf(
                        'The SMS could not be sent to this user %s as it is not a valid international phone number',
                        implode(', ', $response['invalidReceivers'])
                    )
                );
            }
        } catch (ClientException $exception) {
            throw new FailToSendSMSException($exception->getMessage());
        } catch (ServerException $exception) {
            throw new FailToSendSMSException($exception->getMessage());
        }
    }
}
