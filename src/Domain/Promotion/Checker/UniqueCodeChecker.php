<?php

namespace Proximum\Vimeet\Domain\Promotion\Checker;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class UniqueCodeChecker
{
    /**
     * @var PromotionCodeRepositoryInterface
     */
    private $promotionCodeRepository;

    /**
     * UniqueCodeChecker constructor.
     *
     * @param PromotionCodeRepositoryInterface $promotionCodeRepository
     */
    public function __construct(PromotionCodeRepositoryInterface $promotionCodeRepository)
    {
        $this->promotionCodeRepository = $promotionCodeRepository;
    }

    /**
     * @param PromotionCode $promotionCode
     *
     * @return bool
     */
    public function hasUniqueCode(PromotionCode $promotionCode)
    {
        return empty($this->promotionCodeRepository->findDuplicate($promotionCode));
    }

    /**
     * @param Event  $event
     * @param string $code
     *
     * @return bool
     */
    public function exists(Event $event, $code)
    {
        return !empty($this->promotionCodeRepository->findByEventAndCode($event, $code));
    }
}
