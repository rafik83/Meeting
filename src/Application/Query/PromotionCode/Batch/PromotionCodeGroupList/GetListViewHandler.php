<?php

namespace Proximum\Vimeet\Application\Query\PromotionCode\Batch\PromotionCodeGroupList;

use Proximum\Vimeet\Application\Query\PromotionCode\Batch\CanBeUpdatable;
use Proximum\Vimeet\Application\Query\Query;
use Proximum\Vimeet\Domain\Repository\PromotionCodeGroupRepositoryInterface;

class GetListViewHandler implements Query
{
    /** @var CanBeUpdatable */
    private $canBeUpdatable;

    /** @var PromotionCodeGroupRepositoryInterface */
    private $promotionCodeGroupRepository;

    public function __construct(
        CanBeUpdatable $canBeUpdatable,
        PromotionCodeGroupRepositoryInterface $promotionCodeGroupRepository
    ) {
        $this->canBeUpdatable = $canBeUpdatable;
        $this->promotionCodeGroupRepository = $promotionCodeGroupRepository;
    }

    /**
     * @param GetListView $getListView
     *
     * @return PromotionCodeGroupListView[]
     */
    public function handle(GetListView $getListView): array
    {
        $promotionCodeGroupListViews = [];
        $promotionCodeGroups = $this->promotionCodeGroupRepository->findByEvent($getListView->event);

        foreach ($promotionCodeGroups as $promotionCodeGroup) {
            $promotionCodeGroupListViews[] = new PromotionCodeGroupListView(
                $promotionCodeGroup->getId(),
                $promotionCodeGroup->getTitle(),
                $promotionCodeGroup->getNumber(),
                $promotionCodeGroup->getPrefix(),
                $promotionCodeGroup->getStock(),
                $promotionCodeGroup->getValidUntil(),
                $this->canBeUpdatable->isSatisfiableBy($promotionCodeGroup)
            );
        }

        return $promotionCodeGroupListViews;
    }
}
