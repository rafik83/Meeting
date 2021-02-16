<?php

namespace Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query;

use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeaderView;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\User\Event\ExtraData\Type;

/**
 * When the SAVE API is called to add/update a participant
 * A ZL_LEADER field is needed (sometimes) to indicate who is the owner (leader in LENI's term) of the Sheet
 * Therefore the fields need to be prepare to be added to the call
 */
class PrepareLeaderDataHandler
{
    /** @var ExtraDataRepositoryInterface */
    private $extraDataRepository;

    public function __construct(ExtraDataRepositoryInterface $extraDataRepository)
    {
        $this->extraDataRepository = $extraDataRepository;
    }

    public function handle(PrepareLeaderData $command): ?LeaderView
    {
        if ($command->sheet->isOwner($command->user)) {
            return null;
        }

        $userParticipant = $command->sheet->getUserParticipant($command->user);

        if (!$userParticipant instanceof Participant) {
            return null;
        }

        $participantsSortedById = $command->sheet->getParticipantsArray();
        usort($participantsSortedById, function (Participant $participantA, Participant $participantB) {
            return $participantA->getId()  > $participantB->getId();
        });

        $firstParticipant = reset($participantsSortedById);

        if (!$firstParticipant instanceof Participant || $firstParticipant->getId() === $userParticipant->getId()) {
            return null;
        }

        $leniLeaderUserId = $this->extraDataRepository->getExtraDataForEventNameAndUser(
            $command->sheet->getEvent(),
            Type::LENI_USER_ID,
            $firstParticipant->getUser()
        );

        if (null === $leniLeaderUserId) {
            return null;
        }

        return new LeaderView(
            $leniLeaderUserId->getValue(),
            $firstParticipant->getEmail(),
            $firstParticipant->getUser()->getFirstName(),
            $firstParticipant->getUser()->getLastName(),
            $command->sheet->getTitle()
        );
    }
}
