<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Participant;

use Proximum\Vimeet\Application\Exception\Participant\CanNotRemoveAllParticipantsException;
use Proximum\Vimeet\Domain\Cart\CartManager;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class RemoveHandler
{
    /**
     * @var ParticipantRepositoryInterface
     */
    private $participantRepository;

    /**
     * @var CartManager
     */
    private $cartManager;

    /**
     * @param ParticipantRepositoryInterface $participantRepository
     * @param CartManager                    $cartManager
     */
    public function __construct(ParticipantRepositoryInterface $participantRepository, CartManager $cartManager)
    {
        $this->participantRepository = $participantRepository;
        $this->cartManager           = $cartManager;
    }

    /**
     * @param Remove $remove
     *
     * @throws CanNotRemoveAllParticipantsException
     */
    public function handle(Remove $remove)
    {
        if (count($remove->participants) === $remove->sheet->countParticipants()) {
            throw new CanNotRemoveAllParticipantsException('All participants can not be selected to be remove');
        }

        foreach ($remove->participants as $participant) {
            $remove->sheet->removeParticipant($participant);
            $this->participantRepository->delete($participant);
        }

        // Update cart
        $this->cartManager->updateParticipantsQuantity($remove->sheet);
    }
}
