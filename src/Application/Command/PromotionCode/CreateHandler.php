<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class CreateHandler
{
    /** @var PromotionCodeFactory */
    private $promotionCodeFactory;

    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    public function __construct(
        PromotionCodeFactory $promotionCodeFactory,
        PromotionCodeRepositoryInterface $promotionCodeRepository
    ) {
        $this->promotionCodeFactory = $promotionCodeFactory;
        $this->promotionCodeRepository = $promotionCodeRepository;
    }

    public function handle(Create $create): CreateResult
    {
        $promotionCode = $this->promotionCodeFactory->create(
            $create->event,
            $create->title,
            $create->code,
            $create->stock,
            $create->validUntil,
            $create->translations,
            $create->promotions
        );

        $this->promotionCodeRepository->add($promotionCode);

        return new CreateResult($promotionCode);
    }
}
