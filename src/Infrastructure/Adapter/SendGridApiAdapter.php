<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Command\Messaging\Campaign\ReceiverView;
use Proximum\Vimeet\Application\Exception\Messaging\CampaignSendingFailedException;
use Proximum\Vimeet\Domain\Messaging\SendGridApiClient;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use SendGrid\Content;
use SendGrid\Email;
use SendGrid\Mail;
use SendGrid\Personalization;

class SendGridApiAdapter
{
    /**
     * The maximum number of receivers for a sending.
     *
     * @var int
     */
    const MAX_RECEIVERS = 1000;

    /**
     * @var SendGridApiClient
     */
    private $client;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var EventSender
     */
    private $eventSender;

    /**
     * @param SendGridApiClient $client
     * @param \Twig_Environment $twig
     * @param EventSender       $eventSender
     */
    public function __construct(SendGridApiClient $client, \Twig_Environment $twig, EventSender $eventSender)
    {
        $this->client      = $client;
        $this->twig        = $twig;
        $this->eventSender = $eventSender;
    }

    /**
     * Sends an emailing message to a given list of receivers.
     *
     * @param Message $message   The message to send
     * @param array   $receivers An array of ReceiverView instances indexed by email
     *
     * @throws CampaignSendingFailedException
     */
    public function send(Message $message, array $receivers)
    {
        $rawMail = $this->transform($message);

        foreach (array_chunk($receivers, self::MAX_RECEIVERS, true) as $receiversChunk) {
            $this->doSend($this->prepare(clone $rawMail, $receiversChunk));
        }
    }

    /**
     * Transforms a Message to a SendGrid Mail.
     *
     * @param Message $message
     *
     * @return Mail
     */
    private function transform(Message $message)
    {
        $event = $message->getEvent();
        $body  = $this->twig->load($message->getTemplate())->render(['mail' => $message]);

        $mail = new Mail();
        $mail->setSubject($message->getSubject());
        $mail->setFrom(new Email($event->getTitle(), $this->eventSender->generate($event)));
        $mail->addContent(new Content('text/html', $body));

        return $mail;
    }

    /**
     * Adds receivers and substitutions to a given SendGrid Mail.
     *
     * @param Message $message
     * @param array   $receivers An array of ReceiverView instances indexed by email
     *
     * @return Mail
     */
    private function prepare(Mail $mail, array $receivers)
    {
        /* @var ReceiverView */
        foreach ($receivers as $email => $receiver) {
            $personalization = new Personalization();
            $personalization->addTo(new Email(null, $email));

            foreach ($receiver->getReplaces() as $placeholder => $value) {
                $personalization->addSubstitution($placeholder, $value);
            }

            $mail->addPersonalization($personalization);
        }

        return $mail;
    }

    /**
     * Sends a SendGrid Mail through the SendGridApi
     *
     * @param Mail $mail
     *
     * @throws CampaignSendingFailedException
     */
    private function doSend(Mail $mail)
    {
        $response   = $this->client->send($mail);
        $statusCode = (int) $response->statusCode();
        $statusText = json_decode($response->body(), true);

        if ($statusCode < 200 || $statusCode >= 300) {
            $message = 'An error occured while calling the SendGrid API, please retry later. ';

            if (isset($statusText['errors'])) {
                $message .= sprintf('Reason: %s', $statusText['errors'][0]['message']);
            }

            throw new CampaignSendingFailedException($message);
        }
    }
}
