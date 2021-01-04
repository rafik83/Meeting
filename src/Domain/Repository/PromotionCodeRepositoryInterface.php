<?php

namespace Proximum\Vimeet\Domain\Repository;

use Proximum\Vimeet\Application\View\PromotionCode\Group\PromotioCodeExportedView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;

interface PromotionCodeRepositoryInterface
{
    /**
     * @param PromotionCode $promotionCode
     */
    public function add(PromotionCode $promotionCode);

    /**
     * @param PromotionCode $promotionCode
     */
    public function set(PromotionCode $promotionCode);

    /**
     * @param Event $event
     *
     * @return PromotionCode[]
     */
    public function findWithoutGroupByEvent(Event $event): array;

    /**
     * @param Event $event
     *
     * @return PromotionCode[]
     */
    public function findByEvent(Event $event): array;

    /**
     * @param Event $event
     *
     * @return PromotionCode[]
     */
    public function findBoughtByEvent(Event $event): array;

    /**
     * @param PromotionCode $promotionCode
     *
     * @return PromotionCode[]
     */
    public function findDuplicate(PromotionCode $promotionCode);

    /**
     * @param Event  $event
     * @param string $code
     *
     * @return PromotionCode
     */
    public function findByEventAndCode(Event $event, $code);

    /**
     * @param PromotionCodeGroup $promotionCodeGroup
     *
     * @return PromotioCodeExportedView[]
     */
    public function getPromotionCodeExportedViewByGroup(PromotionCodeGroup $promotionCodeGroup): array;
}
