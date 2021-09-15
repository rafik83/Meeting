<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;

class SelectMessageHandler
{
    /** @var CampaignRepositoryInterface */
    private $campaignRepository;

    /**
     * @param CampaignRepositoryInterface $campaignRepository
     */
    public function __construct(CampaignRepositoryInterface $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * @param SelectMessage $command
     */
    public function handle(SelectMessage $command)
    {
        $campaign = $command->campaign;

        $campaign->setMessage($command->message);

        $this->campaignRepository->set($campaign);
    }
}
