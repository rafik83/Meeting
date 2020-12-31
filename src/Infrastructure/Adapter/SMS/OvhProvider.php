<?php

namespace Proximum\Vimeet\Infrastructure\Adapter\SMS;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Ovh\Api;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\FailToSendSMSException;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\InvalidReceiverException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Infrastructure\Adapter\SMS\Exception\ProviderNotAbleToSendThisTypeOfSMSException;

class OvhProvider implements SMSProviderInterface
{
    /** @var Api */
    private $api;

    /** @var string */
    private $ovhServiceName;

    /** @var string */
    private $ovhSenderName;

    /**
     * @param Api    $api
     * @param string $ovhServiceName
     * @param string $ovhSenderName
     */
    public function __construct(
        Api $api,
        string $ovhServiceName,
        string $ovhSenderName
    ) {
        $this->api = $api;
        $this->ovhServiceName = $ovhServiceName;
        $this->ovhSenderName = $ovhSenderName;
    }

    public function canSend(SMS $sms): bool
    {
        return mb_strpos($sms->getReceiver(), '+1') !== 0;
    }

    /**
     * @param SMS $sms
     *
     * @throws FailToSendSMSException
     * @throws InvalidReceiverException
     * @throws ProviderNotAbleToSendThisTypeOfSMSException
     */
    public function sendMessage(SMS $sms): void
    {
        if (!$this->canSend($sms)) {
            throw new ProviderNotAbleToSendThisTypeOfSMSException(sprintf('%s', $sms->getReceiver()));
        }

        try {
            $content = [
                'message' => $sms->getMessage(),
                'receivers' => [$sms->getReceiver()],
                'sender' => $this->ovhSenderName,
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
