<?php

namespace Proximum\Vimeet\Application\Command\User\Batch;

use Proximum\Vimeet\Application\Components\Sheet\SheetGuesser;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\UserRepositoryInterface;

class BatchCampaignHandler
{
    /** @var SheetGuesser */
    private $sheetGuesser;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var \DateTimeInterface */
    private $datetime;

    public function __construct(
        SheetGuesser $sheetGuesser,
        UserRepositoryInterface $userRepository,
        \DateTimeInterface $datetime
    ) {
        $this->sheetGuesser = $sheetGuesser;
        $this->userRepository = $userRepository;
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

            try {
                $sheet = $this->sheetGuesser->getUserSheet($user, $batch->event, $batch->locale);
            } catch (\Exception $exception) {
                continue;
            }

            $campaign->addSheet($sheet);
        }

        return new BatchCampaignResult($campaign);
    }
}
