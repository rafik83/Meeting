<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Application\Command\Tip;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Application\Command\Tip\Update;
use Proximum\Vimeet\Application\Command\Tip\UpdateHandler;
use Proximum\Vimeet\Domain\Model\Tip\Tip;
use Proximum\Vimeet\Domain\Model\Tip\TipTranslation;
use Proximum\Vimeet\Domain\Repository\TipRepositoryInterface;

class UpdateHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testHandle()
    {
        $tipRepository = $this->prophesize(TipRepositoryInterface::class);

        $tip = new Tip('tipTitle', true, false, true, new \DateTime());

        $tip->setTranslation('locale_1', 'title', 'content');
        $command = new Update($tip);

        $this->assertTrue($tip->hasTranslation('locale_1'));

        $this->assertFalse($tip->hasTranslation('locale_2'));

        $tipRepository->set($tip)->shouldBeCalled();

        $handler = new UpdateHandler($tipRepository->reveal());
        $handler->handle($command);
    }
}
