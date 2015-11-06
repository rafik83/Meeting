<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Sheet;

use Proximum\Vimeet\Application\Command\BaseHandler;
use Proximum\Vimeet\Application\Exception\Data\RequiredDataEmptyException;
use Proximum\Vimeet\Application\Exception\Participant\EmailCanNotBeNullException;
use Proximum\Vimeet\Application\Exception\Sheet\ParticipantAlreadyExistException;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class BuyParticipantHandler extends BaseHandler
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var SheetRepositoryInterface
     */
    private $sheetRepository;

    /**
     * @param UserRepositoryInterface        $userRepository
     * @param ParticipantRepositoryInterface $participantRepository
     * @param SheetRepositoryInterface       $sheetRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ParticipantRepositoryInterface $participantRepository,
        SheetRepositoryInterface $sheetRepository
    ) {
        $this->userRepository        = $userRepository;
        $this->participantRepository = $participantRepository;
        $this->sheetRepository       = $sheetRepository;
    }

    /**
     * @param BuyParticipant $buyParticipant
     *
     * @throws EmailCanNotBeNullException
     * @throws ParticipantAlreadyExistException
     * @throws RequiredDataEmptyException
     */
    public function handle(BuyParticipant $buyParticipant)
    {
        // Check the constraint on the data (required) before
        $this->checkDataConstraint($buyParticipant->participantData, $buyParticipant->sheet->getType()->getParticipantTemplate());

        if ($buyParticipant->participantData['email'] === null) {
            throw new EmailCanNotBeNullException();
        }

        // Try to find user
        $user = $this->userRepository->findByEmail($buyParticipant->participantData['email']);

        // Create user if not exists
        if (null === $user) {
            $user = new User($buyParticipant->participantData['email'], '', '', $buyParticipant->locale);
            $this->userRepository->add($user);
        }

        foreach ($buyParticipant->sheet->getParticipants() as $participant) {
            if ($participant->getUser() == $user) {
                throw new ParticipantAlreadyExistException('User already linked to this sheet');
            }
        }

        $packageTemplate = $buyParticipant->sheet->getTypePackageTemplate();
        $packageData     = $buyParticipant->sheet->getPackageData();
        $shouldUpdate    = false;

        foreach ($packageTemplate as $templateKey => $template) {
            foreach ($template['template'] as $blockKey => $block) {

                //Add +1 on the bought participant option
                if ($block['type'] === 'lib_participant') {
                    $packageData[$templateKey][$blockKey]['participant']        = true;
                    $packageData[$templateKey][$blockKey]['participant_bought'] = isset($packageData[$templateKey][$blockKey]['participant_bought']) ? $packageData[$templateKey][$blockKey]['participant_bought'] + 1 : 1;
                    $shouldUpdate = true;
                }

                //Add the planning if option taken
                if ($block['type'] === 'lib_planning') {
                    if ($buyParticipant->participantBuyOption['planning'] === true) {
                        $packageData[$templateKey][$blockKey]['planning']        = true;
                        $packageData[$templateKey][$blockKey]['planning_bought'] = isset($packageData[$templateKey][$blockKey]['planning_bought']) ? $packageData[$templateKey][$blockKey]['planning_bought'] + 1 : 1;

                        $shouldUpdate = true;
                    }
                }
            }
        }

        if ($shouldUpdate === true) {
            $buyParticipant->sheet->setPackageData($packageData);
            $this->sheetRepository->set($buyParticipant->sheet);
        }

        // Add the new participant
        $participant = new Participant($buyParticipant->sheet, $user, $buyParticipant->participantData['data'], $buyParticipant->owner);
        $this->participantRepository->add($participant);

        $buyParticipant->participant = $participant;
    }
}
