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

        $tip = new Tip('tipTitle', true, false, true);
        $tipTranslation1 = new TipTranslation($tip, 'title', 'locale_1', 'content');
        $tip->translations = new ArrayCollection();

        $tip->translations->set('locale_1', $tipTranslation1);
        $command = new Update($tip);

        $this->assertTrue($tip->translations->containsKey('locale_1'));

        $this->assertFalse($tip->translations->containsKey('locale_2'));

        $tipRepository->set($tip)->shouldBeCalled();

        $handler = new UpdateHandler($tipRepository->reveal());
        $handler->handle($command);
    }
}