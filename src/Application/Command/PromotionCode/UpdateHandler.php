<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode;

class UpdateHandler extends AbstractCommandHandler
{
    /**
     * @param Update $command
     */
    public function handle(Update $command)
    {
        $command->promotionCode->update($command->title, $command->code, $command->stock, $command->validUntil);

        $this->checkUniqueCode($command->promotionCode);
        $this->translate($command->promotionCode, $command);
        $this->setPromotions($command->promotionCode, $command);

        $this->promotionCodeRepository->set($command->promotionCode);
    }
}
