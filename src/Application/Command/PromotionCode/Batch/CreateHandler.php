<?php

namespace Proximum\Vimeet\Application\Command\PromotionCode\Batch;

use Proximum\Vimeet\Application\Command\PromotionCode\PromotionCodeFactory;
use Proximum\Vimeet\Domain\Model\PromotionCodeGroup;
use Proximum\Vimeet\Domain\Promotion\Generator\CodeGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeGroupRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class CreateHandler
{
    /** @var CodeGeneratorInterface */
    private $codeGenerator;

    /** @var PromotionCodeFactory */
    private $promotionCodeFactory;

    /** @var PromotionCodeGroupRepositoryInterface */
    private $promotionCodeGroupRepository;

    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    /** @var \DateTimeInterface */
    private $dateTime;

    public function __construct(
        CodeGeneratorInterface $codeGenerator,
        PromotionCodeFactory $promotionCodeFactory,
        PromotionCodeGroupRepositoryInterface $promotionCodeGroupRepository,
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        \DateTimeInterface $dateTime
    ) {
        $this->codeGenerator = $codeGenerator;
        $this->promotionCodeFactory = $promotionCodeFactory;
        $this->promotionCodeGroupRepository = $promotionCodeGroupRepository;
        $this->promotionCodeRepository = $promotionCodeRepository;
        $this->dateTime = $dateTime;
    }

    public function handle(Create $create): PromotionCodeGroup
    {
        $promotionCodeGroup = new PromotionCodeGroup(
            $create->event,
            $create->title,
            $create->number,
            $create->prefix,
            $create->stock,
            $create->validUntil,
            $this->dateTime
        );
        $this->promotionCodeGroupRepository->add($promotionCodeGroup);

        for ($increment = 1; $increment <= $create->number; ++$increment) {
            $code = $this->codeGenerator->generate($create->event, $create->prefix);
            $promotionCode = $this->promotionCodeFactory->create(
                $create->event,
                $create->title,
                $code,
                $create->stock,
                $create->validUntil,
                $create->translations,
                $create->promotions,
                $promotionCodeGroup
            );
            $this->promotionCodeRepository->add($promotionCode);
        }

        return $promotionCodeGroup;
    }
}
