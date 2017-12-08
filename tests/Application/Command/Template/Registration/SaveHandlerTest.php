<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Command\Template\Registration;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\Save;
use Proximum\Vimeet\Application\Command\Template\Registration\SaveHandler;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;

class SaveHandlerTest extends TestCase
{
    public function testHandle()
    {
        $registrationTemplate = $this->prophesize(RegistrationTemplate::class);
        $registrationTemplateRepository = $this->prophesize(RegistrationTemplateRepositoryInterface::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $registrationTemplate->setValue(['whatever' => 'data'])->shouldBeCalled();
        $registrationTemplateRepository->set($registrationTemplate->reveal())->shouldBeCalled();
        $jobQueue->indexSheetsByRegistrationTemplate($registrationTemplate->reveal())->shouldBeCalled();

        $saveHandler = new SaveHandler($registrationTemplateRepository->reveal(), $jobQueue->reveal());
        $saveHandler->handle(new Save($registrationTemplate->reveal(), ['whatever' => 'data']));
    }
}
