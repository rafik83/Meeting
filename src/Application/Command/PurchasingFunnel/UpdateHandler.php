<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PurchasingFunnel;

use Proximum\Vimeet\Domain\Repository\PurchasingFunnelRepositoryInterface;

class UpdateHandler
{
    /**
     * @var PurchasingFunnelRepositoryInterface
     */
    private $purchasingFunnelRepository;

    /**
     * UpdateHandler constructor.
     *
     * @param PurchasingFunnelRepositoryInterface $purchasingFunnelRepository
     */
    public function __construct(PurchasingFunnelRepositoryInterface $purchasingFunnelRepository)
    {
        $this->purchasingFunnelRepository = $purchasingFunnelRepository;
    }

    /**
     * @param Update $update
     */
    public function handle(Update $update)
    {
        $update->purchasingFunnel
            ->setTitle($update->title)
            ->enable($update->plans->enabled, $update->participantAndPlanning->enabled, $update->options->enabled)
            ->choosePlans($update->plans->plans)
            ->chooseParticipant($update->participantAndPlanning->participant)
            ->choosePlanning($update->participantAndPlanning->planning)
            ->chooseOptions($update->options->options)
        ;

        foreach ($update->purchasingFunnel->getEvent()->getLocales() as $locale) {
            $update->purchasingFunnel->translate(
                $locale,
                $update->plans->getLabel($locale),
                $update->participantAndPlanning->getLabel($locale),
                $update->options->getLabel($locale)
            );
        }

        $this->purchasingFunnelRepository->set($update->purchasingFunnel);
    }
}
