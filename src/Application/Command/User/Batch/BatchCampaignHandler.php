<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class BatchCampaignHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var CampaignRepositoryInterface */
    private $campaignRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        UserRepositoryInterface $userRepository,
        CampaignRepositoryInterface $campaignRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->userRepository = $userRepository;
        $this->campaignRepository = $campaignRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(BatchCampaign $batch): BatchCampaignResult
    {
        $campaign = new Campaign($batch->event, $batch->campaignTitle, [], $this->dateTime, Campaign::RECIPIENT_USER);
        $users = $this->userRepository->findByIds($batch->ids);

        foreach ($users as $user) {
            $campaign->addUser($user);
        }

        $this->campaignRepository->add($campaign);

        return new BatchCampaignResult($campaign);
    }
}
