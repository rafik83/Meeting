<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode\Batch;

use Proximum\Vimeet\Domain\Model\PromotionCode;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class CreateHandler
{
    /** @var PromotionCodeRepositoryInterface */
    private $promotionCodeRepository;

    public function __construct(PromotionCodeRepositoryInterface $promotionCodeRepository)
    {
        $this->promotionCodeRepository = $promotionCodeRepository;
    }

    public function handle(Create $command)
    {
        $code = '';

        $promotionCode = new PromotionCode(
            $command->event,
            $command->title,
            $code,
            $command->stock,
            $command->validUntil
        );

        $this->checkUniqueCode($promotionCode);
        $this->translate($promotionCode, $command);
        $this->setPromotions($promotionCode, $command);

        $this->promotionCodeRepository->add($promotionCode);

        return new CreateResult($promotionCode);
    }
}
