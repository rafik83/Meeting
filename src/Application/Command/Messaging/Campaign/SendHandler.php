<?php

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
     * @var \DateTimeInterface
     */
    private $date;

    /**
     * SendHandler constructor.
     *
     * @param CampaignRepositoryInterface $campaignRepository
     * @param JobQueueInterface           $jobQueue
     * @param \DateTimeInterface          $date
     */
    public function __construct(
        CampaignRepositoryInterface $campaignRepository,
        JobQueueInterface $jobQueue,
        \DateTimeInterface $date
    ) {
        $this->campaignRepository = $campaignRepository;
        $this->jobQueue           = $jobQueue;
        $this->date               = $date;
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
        $campaign->markAsSent($this->date);
        $this->campaignRepository->set($campaign);
    }
}
