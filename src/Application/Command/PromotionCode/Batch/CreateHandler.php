<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode\Batch;

use Proximum\Vimeet\Application\Command\PromotionCode\PromotionCodeFactory;
use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;
use Proximum\Vimeet\Domain\Promotion\Generator\CodeGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class CreateHandler
{
    /** @var CodeGeneratorInterface */
    private $codeGenerator;

    /** @var PromotionCodeFactory */
    private $promotionCodeFactory;

    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    /** @var UniqueCodeChecker */
    private $uniqueCodeChecker;

    public function __construct(
        CodeGeneratorInterface $codeGenerator,
        PromotionCodeFactory $promotionCodeFactory,
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        UniqueCodeChecker $uniqueCodeChecker
    ) {
        $this->codeGenerator = $codeGenerator;
        $this->promotionCodeFactory = $promotionCodeFactory;
        $this->promotionCodeRepository = $promotionCodeRepository;
        $this->uniqueCodeChecker = $uniqueCodeChecker;
    }

    public function handle(Create $create)
    {
        $code = $this->codeGenerator->generate($create->event, $create->prefix);

        $promotionCode = $this->promotionCodeFactory->create(
            $create->event,
            $create->title,
            $code,
            $create->stock,
            $create->validUntil,
            $create->translations,
            $create->promotions
        );

        $this->promotionCodeRepository->add($promotionCode);

        return;
    }
}
