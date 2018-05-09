<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Happening;

use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Application\Command\Happening\ParticipateHandler;
use Proximum\Vimeet\Application\Exception\Happening\HappeningException;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Repository\HappeningRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\ParticipantRepositoryInterface;

class ParticipateToHappeningsByProduct
{
    /** @var HappeningRepositoryInterface */
    private $happeningRepository;

    /** @var ParticipantRepositoryInterface */
    private $participantRepository;

    /** @var HappeningsNotOverlapped */
    private $happeningsNotOverlapped;

    /** @var ParticipateHandler */
    private $participateHandler;

    public function __construct(
        HappeningRepositoryInterface $happeningRepository,
        HappeningsNotOverlapped $happeningsNotOverlapped,
        ParticipantRepositoryInterface $participantRepository,
        ParticipateHandler $participateHandler
    ) {
        $this->happeningRepository = $happeningRepository;
        $this->happeningsNotOverlapped = $happeningsNotOverlapped;
        $this->participantRepository = $participantRepository;
        $this->participateHandler = $participateHandler;
    }

    public function handle(Product $product, Participant $participant): void
    {
        $happeningsByProduct = $this->happeningRepository->findByProduct($product);
        $happeningsNotOverlapped = $this->happeningsNotOverlapped->getHappeningsNotOverlapped($happeningsByProduct);

        foreach ($happeningsNotOverlapped as $happening) {
            $this->participateToHappening($participant, $happening);
        }
    }

    private function participateToHappening(Participant $participant, Happening $happening): void
    {
        $participantsToHappening = $this->participantRepository->getParticipantsForHappening(
            $participant->getSheet(),
            $happening
        );

        // User already participate to this Happening
        if (\in_array($participant, $participantsToHappening, true)) {
            return;
        }

        // Add the current participant to all participants to this happening
        $participantsToHappening[] = $participant;

        try {
            $this->participateHandler->handle(
                new Participate(
                    $happening,
                    $participant->getSheet(),
                    $participant->getUser(),
                    $participantsToHappening,
                    null,
                    null,
                    false,
                    false
                )
            );
        } catch (HappeningException $happeningException) {
            // Nothing to do
        }
    }
}
