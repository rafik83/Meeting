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
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Domain\Model\User;
use SendGrid\Content;
use SendGrid\Email;
use SendGrid\Mail;
use SendGrid\Personalization;

class SendGridApiAdapter
{
    private $client;
    private $twig;
    private $translator;

    public function __construct(SendGridApiClient $client, \Twig_Environment $twig, TranslatorAdapter $translator)
    {
        $this->client     = $client;
        $this->twig       = $twig;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function send(Message $message, $from, array $receivers)
    {
        foreach ($receivers as $receiver) {
            $this->client->send($this->transform($message, $from, $receiver));
        }
    }

    private function transform(Message $message, $from, $receiver)
    {
        // $template = $this->twig->loadTemplate($mail->getTemplate()); // TODO Habillage ?

        $mail = new Mail();
        $mail->setSubject($message->getSubject());
        $mail->addContent(new Content('text/html', $message->getContent()));
        $mail->setFrom(new Email(null, $from));

        if (!$receiver instanceof User && !$receiver instanceof BillingInfo) {
            throw new \InvalidArgumentException('Each emailing receiver must either be an instance of User or BillingInfo.');
        }

        $personalization = new Personalization();
        $personalization->addTo(new Email(null, $receiver->getEmail()));

        $mail->addPersonalization($personalization);

        return $mail;
    }
}
