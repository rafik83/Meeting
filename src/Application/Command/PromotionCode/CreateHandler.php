<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Model\Promotion;
use Proximum\Vimeet\Domain\Model\PromotionCode;

class CreateHandler extends AbstractCommandHandler
{
    /**
     * @param Create $command
     *
     * @return CreateResult
     */
    public function handle(Create $command)
    {
        $promotionCode = new PromotionCode(
            $command->event,
            $command->title,
            $command->code,
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
