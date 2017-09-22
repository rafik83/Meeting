<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Tip;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class ConfirmationPhoneTipChecker
{
    /**
     * @var TipRepositoryInterface
     */
    private $tipRepository;

    /**
     * ConfirmationPhoneTipChecker constructor.
     *
     * @param TipRepositoryInterface $tipRepository
     */
    public function __construct(TipRepositoryInterface $tipRepository)
    {
        $this->tipRepository = $tipRepository;
    }

    /**
     * @param Event $event
     * @param Type  $type
     *
     * @return bool
     */
    public function isEnabled(Event $event, Type $type): bool
    {
        return $this->tipRepository->isConfirmationPhoneEnabled($event, $type);
    }
}
