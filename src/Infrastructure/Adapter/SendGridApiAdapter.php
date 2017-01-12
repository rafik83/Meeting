<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Domain\Messaging\SendGridApiClient;
use Proximum\Vimeet\Domain\Model\MailRecipientInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use SendGrid\Content;
use SendGrid\Email;
use SendGrid\Mail;
use SendGrid\Personalization;

class SendGridApiAdapter
{
    /**
     * @var SendGridApiClient
     */
    private $client;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @param SendGridApiClient   $client
     * @param Twig_Environment    $twig
     */
    public function __construct(SendGridApiClient $client, \Twig_Environment $twig)
    {
        $this->client = $client;
        $this->twig   = $twig;
    }

    /**
     * Sends an emailing message to a given list of users.
     *
     * @param Message                  $message   The message to send
     * @param string                   $sender    The message sender
     * @param MailRecipientInterface[] $receivers The message receivers
     */
    public function send(Message $message, $sender, array $receivers)
    {
        foreach ($receivers as $receiver) {
            $this->client->send($this->transform($message, $sender, $receiver));
        }
    }

    /**
     * Transforms a Message to a sendgrid Mail.
     *
     * @param Message                $message
     * @param string                 $sender
     * @param MailRecipientInterface $receiver
     *
     * @return Mail
     */
    private function transform(Message $message, $sender, MailRecipientInterface $receiver)
    {
        $mail = new Mail();
        $mail->setSubject($message->getSubject());
        $mail->addContent(new Content('text/html', $this->render($message)));
        $mail->setFrom(new Email($message->getEvent()->getTitle(), $sender));

        $personalization = new Personalization();
        $personalization->addTo(new Email(null, $receiver->getEmail()));

        $mail->addPersonalization($personalization);

        return $mail;
    }

    /**
     * Renders a Message.
     *
     * @param Message $message
     *
     * @return string
     */
    private function render(Message $message)
    {
        return $this->twig->load($message->getTemplate())->render(['mail' => $message]);
    }
}
