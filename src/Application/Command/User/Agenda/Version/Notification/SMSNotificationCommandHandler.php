<?php

namespace Proximum\Vimeet\Application\Command\User\Agenda\Version\Notification;

use Proximum\Vimeet\Application\Adapter\SMSSenderInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Application\Exception\Messaging\SMS\SMSException;
use Proximum\Vimeet\Domain\Messaging\SMS\SMS;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\User\UserEventPhone;
use Proximum\Vimeet\Domain\Repository\User\UserEventPhoneRepositoryInterface;
use Proximum\Vimeet\Domain\User\Agenda\Version\DiffVerbalizer;

class SMSNotificationCommandHandler
{
    /** @var SMSSenderInterface */
    private $SMSSender;

    /** @var TranslatorInterface */
    private $translator;

    /** @var UserEventPhoneRepositoryInterface */
    private $userEventPhoneRepository;

    /** @var EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    public function __construct(
        SMSSenderInterface $SMSSender,
        TranslatorInterface $translator,
        UserEventPhoneRepositoryInterface $userEventPhoneRepository,
        EventUrlGeneratorInterface $eventUrlGenerator
    ) {
        $this->SMSSender = $SMSSender;
        $this->translator = $translator;
        $this->userEventPhoneRepository = $userEventPhoneRepository;
        $this->eventUrlGenerator = $eventUrlGenerator;
    }

    public function handle(SMSNotificationCommand $command): void
    {
        $userEventPhone = $this->userEventPhoneRepository->findValidated($command->user, $command->event);

        if (!$userEventPhone instanceof UserEventPhone) {
            return;
        }

        $locale = $command->sheet->getUserLocale($command->user);

        $startingSentence = $this->translator->trans(
            DiffVerbalizer::TRANSLATION_AGENDA_MODIFIED,
            [],
            DiffVerbalizer::TRANSLATION_DOMAIN,
            $locale
        );

        $agendaUrl = $this->eventUrlGenerator->generateEventAbsoluteUrl(
            $command->event,
            Route::AGENDA_DEFAULT,
            [
                'sheet' => $command->sheet->getId(),
                '_locale' => $command->event->getAvailableLocale($locale),
            ]
        );

        $message = $startingSentence . "\n" . $command->verbalizedDiff . "\n" . $agendaUrl;

        $sms = new SMS($userEventPhone->getPhone(), $message);

        try {
            $this->SMSSender->send($sms);
        } catch (SMSException $exception) {
            // Avoid to break process if the SMS provider does not succeed in sending the SMS
            return;
        }
    }
}
