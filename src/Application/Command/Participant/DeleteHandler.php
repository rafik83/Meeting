<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Exception\Participant\IsNotLinkedToSheetException;
use Proximum\Vimeet\Application\Exception\Participant\IsNotOwnerException;
use Proximum\Vimeet\Application\Exception\Participant\OwnerCanNotBeDeletedException;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Specification\Sheet\CanAccess;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class DeleteHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     */
    public function __construct(ParticipantRepositoryInterface $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    /**
     * @param Delete $delete
     *
     * @throws IsNotLinkedToSheetException
     * @throws IsNotOwnerException
     * @throws OwnerCanNotBeDeletedException
     */
    public function handle(Delete $delete)
    {
        $sheetSpecification = new CanAccess($delete->requester);

        if (!$sheetSpecification->isSatisfiedBy($delete->sheet)) {
            throw new IsNotLinkedToSheetException('No participant for this user attached on this sheet');
        }

        $participant = $this->participantRepository->findById($delete->participantId);

        if ($participant->isOwner()) {
            throw new OwnerCanNotBeDeletedException('The participant selected to be deleted is owner of the sheet');
        }

        $requesterParticipant = $this->participantRepository->getParticipantForUserAndSheet($delete->requester, $delete->sheet);

        if (!$requesterParticipant->isOwner()) {
            throw new IsNotOwnerException('The requester is not owner of the sheet and therefore can not delete a participant');
        }

        $this->participantRepository->delete($participant);
    }
}
