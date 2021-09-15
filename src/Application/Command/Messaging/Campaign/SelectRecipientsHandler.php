<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;

class SelectRecipientsHandler
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
     * @param SelectRecipients $command
     */
    public function handle(SelectRecipients $command)
    {
        $campaign = $command->campaign;
        foreach ($command->recipients as $recipient) {
            $campaign->addRecipient($recipient);
        }

        $this->campaignRepository->set($campaign);
    }
}
