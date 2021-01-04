<?php

namespace Proximum\Vimeet\Application\Command\Package\PromotionCode;

use Proximum\Vimeet\Domain\Repository\PromotionCodeRowRepositoryInterface;

class RemoveHandler
{
    /**
     * @var PromotionCodeRowRepositoryInterface
     */
    private $promotionCodeRowRepository;

    /**
     * RemoveHandler constructor.
     *
     * @param PromotionCodeRowRepositoryInterface $promotionCodeRowRepository
     */
    public function __construct(PromotionCodeRowRepositoryInterface $promotionCodeRowRepository)
    {
        $this->promotionCodeRowRepository = $promotionCodeRowRepository;
    }

    /**
     * @param Remove $remove
     */
    public function handle(Remove $remove)
    {
        $this->promotionCodeRowRepository->delete($remove->promotionCodeRow);
    }
}
