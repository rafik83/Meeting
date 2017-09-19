<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Planner\Callback;

use Proximum\Vimeet\Domain\Repository\PlannerJobRepositoryInterface;

class SetStatusHandler
{
    /** @var PlannerJobRepositoryInterface */
    private $plannerJobRepository;

    /** @var string */
    private $plannerTrustedName;

    /**
     * @param PlannerJobRepositoryInterface $plannerJobRepository
     * @param string                        $plannerTrustedName
     */
    public function __construct(PlannerJobRepositoryInterface $plannerJobRepository, string $plannerTrustedName)
    {
        $this->plannerJobRepository = $plannerJobRepository;
        $this->plannerTrustedName = $plannerTrustedName;
    }

    /**
     * @param SetStatus $setStatus
     */
    public function handle(SetStatus $setStatus)
    {
        if ($this->plannerTrustedName !== $setStatus->name) {
            throw new \InvalidArgumentException(sprintf('Given build name %s is not trusted', $setStatus->name));
        }

        $plannerJob = $this->plannerJobRepository->findByFilename($setStatus->filepath);

        if (null === $plannerJob) {
            throw new \InvalidArgumentException(sprintf('PlannerJob not found with file: %s', $setStatus->filepath));
        }

        if ($setStatus->isPhaseCompleted()) {
            // do nothing
            return;
        }

        if ($setStatus->isPhaseFinalized() && $setStatus->isStatusSuccess()) {
            $plannerJob->setSuccess();
            // import meetings

        } elseif ($setStatus->isPhaseQueued()) {
            $plannerJob->setQueued();
        } elseif ($setStatus->isPhaseStarted()) {
            $plannerJob->setStarted();
        } elseif ($setStatus->isPhaseFinalized() && $setStatus->isStatusFailure()) {
            $plannerJob->setError('flash.admin.planner.export.plannerError');
        } elseif ($setStatus->isPhaseFinalized() && $setStatus->isStatusAborted()) {
            $plannerJob->setAborted();
        }

        $this->plannerJobRepository->set($plannerJob);
    }
}
