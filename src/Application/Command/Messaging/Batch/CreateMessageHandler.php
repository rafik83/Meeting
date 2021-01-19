<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Batch;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Model\Messaging\Message;
use Proximum\Vimeet\Ui\Bundle\MailBundle\Mail\Messaging\MessageContentMail;

class CreateMessageHandler
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \DateTimeInterface
     */
    private $dateTime;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * CreateHandler constructor.
     *
     * @param \Twig_Environment   $twig
     * @param TranslatorInterface $translator
     * @param \DateTimeInterface  $dateTime
     */
    public function __construct(
        \Twig_Environment $twig,
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
