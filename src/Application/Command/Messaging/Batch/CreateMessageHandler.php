<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;

use DateTimeInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Messaging\MessageContentMail;
use Twig\Environment;

class CreateMessageHandler
{
    private Environment $twig;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        Environment $twig,
        TranslatorInterface $translator,
        \DateTimeInterface $dateTime
    ) {
        $this->twig       = $twig;
        $this->dateTime   = $dateTime;
        $this->translator = $translator;
    }

    public function handle(CreateMessage $command): Message
    {
        $message = new Message(
            $command->event,
            $this->dateTime,
            $command->name,
            $command->sendToEmailTeam,
            $command->sendEmailToBillingInfo
        );

        foreach ($command->event->getLocales() as $locale) {
            $emailSubject = $this->translator->trans(
                $command->subject,
                [], // no parameters because substitutions provider will do it itself
                'mail',
                $locale
            );

            $emailContent = $this->twig
                ->load($command->emailTemplate)
                ->render(['mail' => new MessageContentMail($message, $command->event, $locale)]);

            $message->translate($locale, $emailSubject, $emailContent, $this->dateTime);
        }

        return $message;
    }
}
