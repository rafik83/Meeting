<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Domain\Tip;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;
use Proximum\Vimeet\Domain\Tip\ConfirmationPhoneTipChecker;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class ConfirmationPhoneTipCheckerTest extends TestCase
{
    public function testIsEnabled()
    {
        $event = EventFactory::createEvent();
        $type  = new Type($event);

        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $tipRepository->isConfirmationPhoneEnabled($event, $type)
            ->shouldBeCalled()
            ->willReturn(true);

        $confirmationPhoneTipChecker = new ConfirmationPhoneTipChecker(
            $tipRepository->reveal()
        );

        $confirmationPhoneTipChecker->isEnabled($event, $type);
    }
}
