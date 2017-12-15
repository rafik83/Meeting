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
use Proximum\Vimeet\Domain\Template\Registration\RegistrationTemplateValidator;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SaveHandlerTest extends TestCase
{
    public function testHandle()
    {
        $registrationTemplate = $this->prophesize(RegistrationTemplate::class);
        $registrationTemplateRepository = $this->prophesize(RegistrationTemplateRepositoryInterface::class);
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $registrationTemplate->getFallback()->shouldBeCalled()->willReturn('fr');
        $registrationTemplate->setValue(['whatever' => 'data'])->shouldBeCalled();
        $registrationTemplateRepository->set($registrationTemplate->reveal())->shouldBeCalled();

        $templateData = $this->prophesize(TemplateData::class);
        $templateDataFactory
            ->createRegistrationFromTemplate($registrationTemplate->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $registrationTemplateValidator = $this->prophesize(RegistrationTemplateValidator::class);
        $registrationTemplateValidator->validate($templateData->reveal());

        $jobQueue->indexSheetsByRegistrationTemplate($registrationTemplate->reveal())->shouldBeCalled();

        $saveHandler = new SaveHandler(
            $registrationTemplateRepository->reveal(),
            $jobQueue->reveal(),
            $templateDataFactory->reveal(),
            $registrationTemplateValidator->reveal()
        );
        $saveHandler->handle(new Save($registrationTemplate->reveal(), ['whatever' => 'data']));
    }
}
