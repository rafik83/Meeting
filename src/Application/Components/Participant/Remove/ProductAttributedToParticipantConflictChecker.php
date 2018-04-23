<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Components\Participant\Remove;

use Proximum\Vimeet\Domain\Repository\CartRowParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ProductAttributedToParticipantRepositoryInterface;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;

/**
 * This method checks if a participant has a conflict with a ProductAttributedToParticipant or a CartRowParticipant
 */
class ProductAttributedToParticipantConflictChecker
{
    /** @var CartRowParticipantRepositoryInterface */
    private $cartRowParticipantRepository;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var ProductAttributedToParticipantRepositoryInterface */
    private $productAttributedToParticipantRepository;

    /**
     * @param ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository
     * @param CartRowParticipantRepositoryInterface             $cartRowParticipantRepository
     * @param ParticipantInfoGuesser                            $participantInfoGuesser
     */
    public function __construct(
        ProductAttributedToParticipantRepositoryInterface $productAttributedToParticipantRepository,
        CartRowParticipantRepositoryInterface $cartRowParticipantRepository,
        ParticipantInfoGuesser $participantInfoGuesser
    ) {
        $this->cartRowParticipantRepository = $cartRowParticipantRepository;
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->productAttributedToParticipantRepository = $productAttributedToParticipantRepository;
    }

    public function getParticipantsWithConflictOnProductAttributed(array $participants, string $locale): ConflictsView
    {
        $conflictsView = new ConflictsView();
        $participantsWithConflict = [];

        $productAttributedToParticipants = $this->productAttributedToParticipantRepository->findByParticipants($participants);

        foreach ($productAttributedToParticipants as $productAttributedToParticipant) {
            $participant = $productAttributedToParticipant->getParticipant();

            if (!isset($participantsWithConflict[$participant->getId()])) {
                $participantsWithConflict[$participant->getId()] = $participant;

                $participantConflictView = new ParticipantConflictView(
                    $participant->getId(),
                    $this->participantInfoGuesser->guessParticipantCompleteName($participant, $locale),
                    $productAttributedToParticipant->getProduct()->getTitle($locale)
                );

                $conflictsView->addConflict($participantConflictView);
            }
        }

        if (!empty($participantsWithConflict)) {
            return $conflictsView;
        }

        $cartRows = $this->cartRowParticipantRepository->findCartRowOnAttributableProductForParticipants($participants);

        if (empty($cartRows)) {
            return $conflictsView;
        }

        foreach ($cartRows as $cartRowParticipant) {
            $participant = $cartRowParticipant->getParticipant();

            if (!isset($participantsWithConflict[$participant->getId()])) {
                $participantConflictView = new ParticipantConflictView(
                    $participant->getId(),
                    $this->participantInfoGuesser->guessParticipantCompleteName($participant, $locale),
                    $cartRowParticipant->getCartRow()->getProduct()->getTitle($locale)
                );

                $conflictsView->addConflict($participantConflictView);

                $participantsWithConflict[$participant->getId()] = $participant;
            }
        }

        return $conflictsView;
    }
}
