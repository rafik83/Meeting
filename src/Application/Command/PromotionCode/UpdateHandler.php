<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\Command\PromotionCode;

use Proximum\Vimeet\Domain\Promotion\Checker\UniqueCodeChecker;
use Proximum\Vimeet\Domain\Repository\OrderRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\PromotionCodeRepositoryInterface;

class UpdateHandler extends AbstractCommandHandler
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    public function __construct(
        PromotionCodeRepositoryInterface $promotionCodeRepository,
        UniqueCodeChecker $uniqueCodeChecker,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($promotionCodeRepository, $uniqueCodeChecker);

        $this->orderRepository = $orderRepository;
    }
    /**
     * @param Update $command
     */
    public function handle(Update $command): void
    {
        $command->promotionCode->update($command->title, $command->code, $command->stock, $command->validUntil);

        $this->checkUniqueCode($command->promotionCode);
        $this->translate($command->promotionCode, $command);

        if (!$this->orderRepository->hasOrderWithPromotionCode($command->promotionCode)) {
            $this->setPromotions($command->promotionCode, $command);
        }

        $this->promotionCodeRepository->set($command->promotionCode);
    }
}
