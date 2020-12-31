<?php

namespace Proximum\Vimeet\Application\Command\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;

class CreateHandler
{
    /** @var SheetRepositoryInterface */
    private $sheetRepository;

    /** @var CampaignRepositoryInterface */
    private $campaignRepository;

    /**
     * @param SheetRepositoryInterface    $sheetRepository
     * @param CampaignRepositoryInterface $campaignRepository
     */
    public function __construct(SheetRepositoryInterface $sheetRepository, CampaignRepositoryInterface $campaignRepository)
    {
        $this->sheetRepository    = $sheetRepository;
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * @param Create $command
     *
     * @return Campaign
     */
    public function handle(Create $command)
    {
        $filter   = function ($value) { return null !== $value && '' !== $value; };
        $campaign = new Campaign($command->event, $command->name, array_filter($command->filters, $filter), new \DateTimeImmutable());

        foreach ($this->sheetRepository->getSheetsById($command->sheetIds) as $sheet) {
            $campaign->addSheet($sheet);
        }

        $this->campaignRepository->add($campaign);

        return $campaign;
    }
}
