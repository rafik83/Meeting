<?php

namespace Proximum\Vimeet\Tests\Application\Command\Template\Registration;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Adapter\JobQueueInterface;
use Proximum\Vimeet\Application\Command\Template\Registration\Save;
use Proximum\Vimeet\Application\Command\Template\Registration\SaveHandler;
use Proximum\Vimeet\Application\Components\Registration\RegistrationTemplateValidatorTranslated;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Registration\RegistrationTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\RegistrationTemplate;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SaveHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $registrationTemplate = $this->prophesize(RegistrationTemplate::class);
        $registrationTemplateRepository = $this->prophesize(RegistrationTemplateRepositoryInterface::class);
        $templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $jobQueue = $this->prophesize(JobQueueInterface::class);

        $registrationTemplate->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $registrationTemplate->getFallback()->shouldBeCalled()->willReturn('fr');
        $registrationTemplate->setValue(['whatever' => 'data'])->shouldBeCalled();
        $registrationTemplateRepository->set($registrationTemplate->reveal())->shouldBeCalled();

        $templateData = $this->prophesize(TemplateData::class);
        $templateDataFactory
            ->createRegistrationFromTemplate($registrationTemplate->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData->reveal())
        ;

        $registrationTemplateValidatorTranslated = $this->prophesize(RegistrationTemplateValidatorTranslated::class);
        $registrationTemplateValidatorTranslated->validate($templateData->reveal());

        $jobQueue->indexSheetsByRegistrationTemplate($registrationTemplate->reveal())->shouldBeCalled();

        $delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $delayedEventDispatcher
            ->dispatch(
                Events::REGISTRATION_TEMPLATE_UPDATED,
                new RegistrationTemplateUpdatedEvent($event->reveal())
            )
            ->shouldBeCalled()
        ;

        $saveHandler = new SaveHandler(
            $registrationTemplateRepository->reveal(),
            $jobQueue->reveal(),
            $templateDataFactory->reveal(),
            $registrationTemplateValidatorTranslated->reveal(),
            $delayedEventDispatcher->reveal()
        );
        $saveHandler->handle(new Save($registrationTemplate->reveal(), ['whatever' => 'data']));
    }
}
