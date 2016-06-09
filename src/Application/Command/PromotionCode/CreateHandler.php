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
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class CreateHandler
{
    /**
     * @var PromotionCodeRepositoryInterface
     */
    private $promotionCodeRepository;

    /**
     * CreateHandler constructor.
     *
     * @param PromotionCodeRepositoryInterface $promotionCodeRepository
     */
    public function __construct(PromotionCodeRepositoryInterface $promotionCodeRepository)
    {
        $this->promotionCodeRepository = $promotionCodeRepository;
    }

    /**
     * @param Create $command
     *
     * @return CreateResult
     */
    public function handle(Create $command)
    {
        $promotionCode = new PromotionCode($command->event, $command->title);

        $this->promotionCodeRepository->add($promotionCode);

        return new CreateResult($promotionCode);
    }
}
