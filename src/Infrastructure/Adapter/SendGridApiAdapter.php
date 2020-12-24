<?php

namespace Proximum\Vimeet\Infrastructure\Adapter;

use Proximum\Vimeet\Application\Adapter\EmailingSenderInterface;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\ReceiverView;
use Proximum\Vimeet\Application\Exception\Messaging\CampaignSendingFailedException;
use Proximum\Vimeet\Domain\Messaging\SendGridApiClient;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\EventSender;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Messaging\MessageContentMail;
use SendGrid\Content;
use SendGrid\Email;
use SendGrid\Mail;
use SendGrid\Personalization;

class SendGridApiAdapter implements EmailingSenderInterface
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
        $rawMails = $this->transform($message);

        foreach (array_chunk($receivers, self::MAX_RECEIVERS, true) as $receiversChunk) {
            $receiverByLocale = [];

            /** @var ReceiverView $receiverOfChunck */
            foreach ($receiversChunk as $receiverOfChunck) {
                // Reindex the receiver by locale and by mail (they were by mail)
                $receiverByLocale[$receiverOfChunck->getLocale()][$receiverOfChunck->getEmail()] = $receiverOfChunck;
            }

            // For each locale, send the chunck of receivers for this locale with the mail
            foreach ($receiverByLocale as $locale => $receiversForLocale) {
                $this->doSend($this->prepare($message, clone $rawMails[$message->getEvent()->getAvailableLocale($locale)], $receiversForLocale));
            }
        }
    }

    /**
     * Transforms a Message to a SendGrid Mail.
     *
     * @param Message $message
     *
     * @return Mail[] indexed by locale (all locales of event)
     */
    private function transform(Message $message)
    {
        $event = $message->getEvent();
        $mails = [];

        foreach ($event->getLocales() as $locale) {
            $body[$locale] = $this
                ->twig
                ->load($message->getTemplate())
                ->render(['mail' => new MessageContentMail($message, $event, $locale)]);

            $mail = new Mail();
            $mail->setSubject($message->getSubject($locale));
            $mail->setFrom(new Email($event->getTitle(), $this->eventSender->generate($event)));
            $mail->addContent(new Content('text/html', $body[$locale]));

            $mails[$locale] = $mail;
        }

        return $mails;
    }

    /**
     * Adds receivers and substitutions to a given SendGrid Mail.
     *
     * @param Message        $message
     * @param Mail           $mail
     * @param ReceiverView[] $receivers An array of ReceiverView instances indexed by email
     *
     * @return Mail
     */
    private function prepare(Message $message, Mail $mail, array $receivers)
    {
        foreach ($receivers as $email => $receiver) {
            $personalization = new Personalization();
            $personalization->addTo(new Email(null, (string) $email));

            // Also send email to team in BCC
            if ($message->isSendToEmailTeam() && null !== $message->getEvent()->getEmailTeam()) {
                $personalization->addBcc(new Email(null, (string) $message->getEvent()->getEmailTeam()));
            }

            foreach ($receiver->getReplaces() as $placeholder => $value) {
                $personalization->addSubstitution((string) $placeholder, (string) $value);
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
