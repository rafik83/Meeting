<?php

namespace Proximum\Vimeet\Application\Command\Event\Participant;

use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipant;
use Proximum\Vimeet\Application\Command\Participant\ConvertToParticipantHandler;
use Proximum\Vimeet\Application\Command\User\ActivateAccount\SendActivateAccountFromLoginToken;
use Proximum\Vimeet\Application\Command\User\ActivateAccount\SendActivateAccountFromLoginTokenHandler;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\User\CompleteProfileEvent;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\TemplateData\ParticipationTypeTemplateDataGetter;
use Proximum\Vimeet\Domain\Model\Participant;

class AddFastCheckinHandler
{
    /** @var ConvertToParticipantHandler */
    private $convertToParticipantHandler;

    /** @var ParticipationTypeTemplateDataGetter */
    private $participationTypeTemplateDataGetter;

    /** @var DelayedEventDispatcherInterface */
    private $eventDispatcher;

    /** @var SendActivateAccountFromLoginTokenHandler */
    private $sendActivateAccountFromLoginTokenHandler;

    public function __construct(
        ConvertToParticipantHandler $convertToParticipantHandler,
        ParticipationTypeTemplateDataGetter $participationTypeTemplateDataGetter,
        DelayedEventDispatcherInterface $delayedEventDispatcher,
        SendActivateAccountFromLoginTokenHandler $sendActivateAccountFromLoginTokenHandler
    ) {
        $this->convertToParticipantHandler = $convertToParticipantHandler;
        $this->participationTypeTemplateDataGetter = $participationTypeTemplateDataGetter;
        $this->eventDispatcher = $delayedEventDispatcher;
        $this->sendActivateAccountFromLoginTokenHandler = $sendActivateAccountFromLoginTokenHandler;
    }

    public function handle(AddFastCheckin $addFastCheckin): ?Participant
    {
        if (null === $addFastCheckin->type) {
            throw new TypeMissingForFastCheckinException();
        }

        $convertToParticipant = new ConvertToParticipant(
            $addFastCheckin->event,
            $addFastCheckin->type,
            $this->guessEmail($addFastCheckin),
            $addFastCheckin->event->getFallback(),
            [
                Tag::PARTICIPANT_FIRSTNAME => $addFastCheckin->firstname,
                Tag::PARTICIPANT_LASTNAME => $addFastCheckin->lastname,
                Tag::SHEET_TITLE => $addFastCheckin->sheetTitle,
                Tag::SHEET_ORGANIZATION => $addFastCheckin->sheetTitle,
                Tag::PARTICIPANT_COUNTRY => $addFastCheckin->country,
                Tag::PARTICIPANT_MOBILE => $addFastCheckin->mobile,
            ],
            $this->participationTypeTemplateDataGetter->getRegistrationTemplateDataByType($addFastCheckin->type),
            $this->participationTypeTemplateDataGetter->getSheetTemplateDataByType($addFastCheckin->type),
            null,
            null,
            $addFastCheckin->hasAccessToMeetings
        );

        $participant = $this->convertToParticipantHandler->handle($convertToParticipant);

        if ($participant === null) {
            return null;
        }

        if ($addFastCheckin->isUserKnown) {
            $completeProfileEvent = new CompleteProfileEvent(
                $participant->getUser(),
                $addFastCheckin->event,
                $participant,
                $addFastCheckin->event->getFallback()
            );
            $this->eventDispatcher->dispatch(Events::USER_PROFILE_COMPLETED, $completeProfileEvent);
        }

        if (!$addFastCheckin->isUserKnown) {
            $sendActivateAccountFromLoginToken = new SendActivateAccountFromLoginToken($participant->getSheet(), $participant->getUser());
            $this->sendActivateAccountFromLoginTokenHandler->handle($sendActivateAccountFromLoginToken);
        }

        return $participant;
    }

    private function guessEmail(AddFastCheckin $addFastCheckin): string
    {
        return '' !== $addFastCheckin->email ? $addFastCheckin->email : $this->generateEmail($addFastCheckin);
    }

    private function generateEmail(AddFastCheckin $addFastCheckin): string
    {
        return 'visitor_' . $this->getToken(12) . '@' . $addFastCheckin->event->getDomain();
    }

    private function getToken($length): string
    {
        $token = '';
        $codeAlphabet = 'abcdefghijklmnopqrstuvwxyz';
        $codeAlphabet .= '0123456789';
        $max = \strlen($codeAlphabet); // edited

        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[random_int(0, $max - 1)];
        }

        return $token;
    }
}
