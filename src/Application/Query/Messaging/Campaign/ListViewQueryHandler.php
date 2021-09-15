<?php

namespace Proximum\Vimeet\Application\Query\Messaging\Campaign;

use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Domain\Model\Messaging\CampaignRepositoryInterface;

class ListViewQueryHandler
{
    /** @var CampaignRepositoryInterface */
    private $campaignRepository;

    /**
     * @param CampaignRepositoryInterface $repository
     */
    public function __construct(CampaignRepositoryInterface $repository)
    {
        $this->campaignRepository = $repository;
    }

    /**
     * @param ListViewQuery $query
     *
     * @return CampaignView[]
     */
    public function handle(ListViewQuery $query)
    {
        $event = $query->getEvent();

        return array_map(function (Campaign $campaign) {
            return CampaignView::createFromCampaign($campaign);
        }, $this->campaignRepository->findByEvent($event));
    }
}
