<?php

namespace Proximum\Vimeet\Application\Query\Meeting;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Rule\ParticipantInfoAccessRulesResolver;
use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Application\View\Meeting\MeetingParticipantView;
use Proximum\Vimeet\Domain\Model\Contact;
use Proximum\Vimeet\Domain\Repository\ContactRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

class ParticipantsViewQueryHandler
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var ParticipantInfoAccessRulesResolver */
    private $participantInfoAccessRulesResolver;

    /** @var TranslatorInterface */
    private $translator;

    /** @var ContactRepositoryInterface */
    private $contactRepository;

    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        ParticipantInfoAccessRulesResolver $participantInfoAccessRulesResolver,
        TranslatorInterface $translator,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->participantInfoAccessRulesResolver = $participantInfoAccessRulesResolver;
        $this->translator = $translator;
        $this->contactRepository = $contactRepository;
    }

    /**
     * @param ParticipantsViewQuery $query
     *
     * @return MeetingParticipantView[]
     */
    public function handle(ParticipantsViewQuery $query): array
    {
        $meetingParticipantViews = [];
        $event = $query->seerSheet->getEvent();

        $insufficientEvaluationMessage = $this->translator->trans(
            'event.meeting.listRequest.contact.insufficient_evaluation',
            [],
            'messages',
            $query->locale
        );

        $unavailableMessage = $this->translator->trans(
            'event.meeting.listRequest.contact.unavailable',
            [],
            'messages',
            $query->locale
        );

        foreach ($query->participants as $participant) {
            $reverseEvaluation = null;

            $participantInfo = $this->participantInfoGuesser->guessParticipantInfos(
                $participant,
                $query->locale
            );

            $participantContactEvaluation = [];
            $participantContactComment = [];

            // check if evaluation from current user is sufficient to access participant info
            $participantInfoAccessRule = $this->participantInfoAccessRulesResolver->getParticipantInfoAccessRule(
                $query->seerSheet,
                $participant->getSheet()
            );

            $ownEvaluation = null;

            foreach ($query->contacts as $contact) {
                if ($participant->getUser()->getId() !== $contact->getContact()->getId()) {
                    continue;
                }

                if ($contact->hasEvaluation()) {
                    $participantContactEvaluation[] = $this->formatContactValue($contact, $contact->getEvaluation());
                    if ($contact->getUser()->getId() === $query->seerUser->getId()) {
                        $ownEvaluation = $contact->getEvaluation();
                    }
                }

                if ($contact->hasComment()) {
                    $participantContactComment[] = $this->formatContactValue($contact, $contact->getComment());
                }
            }

            $participantInfoReverseAccessRule = $this->participantInfoAccessRulesResolver->getParticipantInfoAccessRule(
                $participant->getSheet(),
                $query->seerSheet
            );

            // Get the evaluation from contact to seer user
            $reverseEvaluation = $this->contactRepository->getEvaluationContactByEventAndUser(
                $event,
                $participant->getUser(),
                $query->seerUser
            );

            if (!$participantInfoAccessRule->isPhoneVisible($ownEvaluation)) {
                $phoneValue = $insufficientEvaluationMessage;
            } else if (!$participantInfoReverseAccessRule->isPhoneVisible($reverseEvaluation)) {
                $phoneValue = $unavailableMessage;
            } else {
                $phoneValue = $participantInfo[Tag::PARTICIPANT_PHONE];
            }

            if (!$participantInfoAccessRule->isEmailVisible($ownEvaluation)) {
                $emailValue = $insufficientEvaluationMessage;
            } else if (!$participantInfoReverseAccessRule->isEmailVisible($reverseEvaluation)) {
                $emailValue = $unavailableMessage;
            } else {
                $emailValue = $participant->getEmail();
            }

            $gender = $participantInfo[Tag::PARTICIPANT_GENDER];

            $meetingParticipantViews[] = new MeetingParticipantView(
                $participantInfo[Tag::PARTICIPANT_FIRSTNAME],
                $participantInfo[Tag::PARTICIPANT_LASTNAME],
                $participantInfo[Tag::PARTICIPANT_POSITION],
                $phoneValue,
                $gender ? $this->translator->trans('gender.' . $gender, [], 'messages') : '',
                $emailValue,
                implode("\n", $participantContactEvaluation),
                implode("\n", $participantContactComment)
            );
        }

        return $meetingParticipantViews;
    }

    private function formatContactValue(Contact $contact, string $value): string
    {
        return sprintf(
            '%s: %s',
            $contact->getUser()->getFullname(),
            $value
        );
    }
}
