<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Messaging\SendGridApiClient;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\User;
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
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param SendGridApiClient   $client
     * @param Twig_Environment    $twig
     * @param TranslatorInterface $translator
     */
    public function __construct(SendGridApiClient $client, \Twig_Environment $twig, TranslatorInterface $translator)
    {
        $this->client     = $client;
        $this->twig       = $twig;
        $this->translator = $translator;
    }

    /**
     * Sends an emailing message to a given list of users.
     *
     * @param Message              $message   The message to send
     * @param string               $sender    The message sender
     * @param (User|BillingInfo)[] $receivers The message receivers
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
     * @param Message          $message
     * @param string           $sender
     * @param User|BillingInfo $receiver
     *
     * @return Mail
     */
    private function transform(Message $message, $sender, $receiver)
    {
        $mail = new Mail();
        $mail->setSubject($message->getSubject());
        $mail->addContent(new Content('text/html', $this->render($message)));
        $mail->setFrom(new Email(null, $sender));

        if (!$receiver instanceof User && !$receiver instanceof BillingInfo) {
            throw new \InvalidArgumentException('Each emailing receiver must either be an instance of User or BillingInfo.');
        }

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
