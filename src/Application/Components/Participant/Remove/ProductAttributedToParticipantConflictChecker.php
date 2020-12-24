<?php

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

    private function getConflictViewForProductAttributed(ConflictsView $conflictsView, array &$participants, string $locale): void
    {
        $productAttributedToParticipants = $this->productAttributedToParticipantRepository->findByParticipants($participants);

        foreach ($productAttributedToParticipants as $productAttributedToParticipant) {
            $participant = $productAttributedToParticipant->getParticipant();

            if (!isset($participantsWithConflict[$participant->getId()])) {
                $participantsWithConflict[$participant->getId()] = $participant;

                $participantConflictView = new ParticipantConflictView(
                    $participant->getId(),
                    $this->participantInfoGuesser->guessParticipantCompleteName($participant, $locale)
                );

                $conflictsView->addConflict($participantConflictView);
            }
        }
    }

    private function getConflictViewForCartRowParticipant(ConflictsView $conflictsView, array &$participants, string $locale): void
    {
        $cartRows = $this->cartRowParticipantRepository->findCartRowOnAttributableProductForParticipants($participants);

        foreach ($cartRows as $cartRowParticipant) {
            $participant = $cartRowParticipant->getParticipant();

            if (!isset($participantsWithConflict[$participant->getId()])) {
                $participantConflictView = new ParticipantConflictView(
                    $participant->getId(),
                    $this->participantInfoGuesser->guessParticipantCompleteName($participant, $locale)
                );

                $conflictsView->addConflict($participantConflictView);

                $participantsWithConflict[$participant->getId()] = $participant;
            }
        }
    }

    public function getParticipantsWithConflictOnProductAttributed(array $participants, string $locale): ConflictsView
    {
        $conflictsView = new ConflictsView();

        $this->getConflictViewForProductAttributed($conflictsView, $participants, $locale);

        if ($conflictsView->hasConflict()) {
            return $conflictsView;
        }

        $this->getConflictViewForCartRowParticipant($conflictsView, $participants, $locale);

        return $conflictsView;
    }
}
