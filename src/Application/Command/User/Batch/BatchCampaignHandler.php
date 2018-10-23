<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class BatchCampaignHandler
{
    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var CampaignRepositoryInterface */
    private $campaignRepository;

    /** @var \DateTimeInterface */
    private $datetime;

    public function __construct(
        UserRepositoryInterface $userRepository,
        CampaignRepositoryInterface $campaignRepository,
        \DateTimeInterface $datetime
    ) {
        $this->userRepository = $userRepository;
        $this->campaignRepository = $campaignRepository;
        $this->datetime = $datetime;
    }

    public function handle(BatchCampaign $batch): BatchCampaignResult
    {
        $campaign = new Campaign($batch->event, $batch->campaignTitle, [], $this->datetime);

        foreach ($batch->ids as $id) {
            $user = $this->userRepository->findOneById($id);
            if (!$user instanceof User) {
                continue;
            }

            $campaign->addUser($user);
            $campaign->addRecipient(Campaign::RECIPIENT_USER);
        }

        $this->campaignRepository->add($campaign);

        return new BatchCampaignResult($campaign);
    }
}
