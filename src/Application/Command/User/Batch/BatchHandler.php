<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class BatchHandler
{
    /** @var BatchCampaignHandler */
    private $batchCampaignHandler;

    /** @var UserRepositoryInterface */
    private $userRepository;

    public function __construct(
        BatchCampaignHandler $batchCampaignHandler,
        UserRepositoryInterface $userRepository
    ) {
        $this->batchCampaignHandler = $batchCampaignHandler;
        $this->userRepository = $userRepository;
    }

    public function handle(Batch $batch): BatchResultInterface
    {
        if (Batch::SELECTION_TYPE_ALL === $batch->selectionType) {
            $batch->ids = array_map(function (User $user) {
                return $user->getId();
            }, $this->userRepository->findWithEnabledSheetByEvent($batch->event));
        }

        if ($batch->campaignTitle) {
            return $this->batchCampaignHandler->handle(
                new BatchCampaign(
                    $batch->event,
                    $batch->locale,
                    $batch->ids,
                    $batch->campaignTitle
                )
            );
        }

        throw new \Exception('No handler found');
    }
}
