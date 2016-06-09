<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Promotion\CodeGeneratorInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class CreateHandler
{
    /**
     * @var PromotionCodeRepositoryInterface
     */
    private $promotionCodeRepository;

    /**
     * @var CodeGeneratorInterface
     */
    private $codeGenerator;

    /**
     * CreateHandler constructor.
     *
     * @param PromotionCodeRepositoryInterface $promotionCodeRepository
     * @param CodeGeneratorInterface           $codeGenerator
     */
    public function __construct(
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        CodeGeneratorInterface $codeGenerator
    ) {
        $this->promotionCodeRepository = $promotionCodeRepository;
        $this->codeGenerator           = $codeGenerator;
    }

    /**
     * @param Create $command
     *
     * @return CreateResult
     */
    public function handle(Create $command)
    {
        $promotionCode = new PromotionCode($command->event, $command->title, $this->codeGenerator->generate());

        $this->promotionCodeRepository->add($promotionCode);

        return new CreateResult($promotionCode);
    }
}
