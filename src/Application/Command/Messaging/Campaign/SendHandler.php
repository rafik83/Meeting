<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\Messaging\Campaign;

use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;

class SendHandler
{
    /**
     * @var CampaignRepositoryInterface
     */
    private $campaignRepository;

    /**
     * @var JobQueueInterface
     */
    private $jobQueue;

    /**
     * SendHandler constructor.
     *
     * @param CampaignRepositoryInterface $campaignRepository
     * @param JobQueueInterface           $jobQueue
     */
    public function __construct(CampaignRepositoryInterface $campaignRepository, JobQueueInterface $jobQueue)
    {
        $this->campaignRepository = $campaignRepository;
        $this->jobQueue           = $jobQueue;
    }

    /**
     * @param Send $command
     */
    public function handle(Send $command)
    {
        $campaign = $command->getCampaign();

        // Add job to queue
        $this->jobQueue->sendCampaign($campaign);

        // Mark campaign as sent
        $campaign->markAsSent(new \DateTime());
        $this->campaignRepository->set($campaign);
    }
}
