<?php

namespace Proximum\Vimeet\Tests\Application\Command\Template\Form;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\DelayedEventDispatcherInterface;
use Proximum\Vimeet\Application\Command\Template\Form\Save;
use Proximum\Vimeet\Application\Command\Template\Form\SaveHandler;
use Proximum\Vimeet\Application\Components\Template\Form\FormTemplateValidatorTranslated;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Event\Template\Form\FormTemplateUpdatedEvent;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Exception\NomenclatureMultipleMustBeOfDepthOneException;
use Proximum\Vimeet\Domain\Template\Exception\TemplateException;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateDataFactory;

class SaveHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $formTemplateRepository,
        $templateDataFactory,
        $templateValidatorTranslated,
        $delayedEventDispatcher,
        $template
    ;

    public function setUp()
    {
        $this->formTemplateRepository = $this->prophesize(FormTemplateRepositoryInterface::class);
        $this->templateDataFactory = $this->prophesize(TemplateDataFactory::class);
        $this->templateValidatorTranslated = $this->prophesize(FormTemplateValidatorTranslated::class);
        $this->delayedEventDispatcher = $this->prophesize(DelayedEventDispatcherInterface::class);
        $this->template = $this->prophesize(FormTemplate::class);
    }

    public function testHandleNotValid(): void
    {
        $this->expectException(TemplateException::class);

        $this->template->setValue(['value'])->shouldBeCalled();
        $this->template->getFallback()->shouldBeCalled()->willReturn('fr');

        $templateData = $this->prophesize(TemplateData::class);
        $this->templateDataFactory->createFormTemplateFromTemplate($this->template->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;
        $exception = new NomenclatureMultipleMustBeOfDepthOneException([]);
        $this->templateValidatorTranslated->validate($templateData->reveal())->shouldBeCalled()->willThrow($exception);

        $save = new Save($this->template->reveal(), ['value']);

        $handler = new SaveHandler(
            $this->formTemplateRepository->reveal(),
            $this->templateDataFactory->reveal(),
            $this->templateValidatorTranslated->reveal(),
            $this->delayedEventDispatcher->reveal()
        );

        $handler->handle($save);
    }

    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $this->template->setValue(['value'])->shouldBeCalled();
        $this->template->getFallback()->shouldBeCalled()->willReturn('fr');
        $this->template->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $templateData = $this->prophesize(TemplateData::class);
        $this->templateDataFactory->createFormTemplateFromTemplate($this->template->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn($templateData)
        ;
        $this->templateValidatorTranslated->validate($templateData->reveal())->shouldBeCalled();
        $this->formTemplateRepository->update($this->template->reveal())->shouldBeCalled();
        $this->delayedEventDispatcher
            ->dispatch(
                Events::FORM_TEMPLATE_UPDATED,
                new FormTemplateUpdatedEvent($event->reveal())
            )->shouldBeCalled()
        ;

        $save = new Save($this->template->reveal(), ['value']);

        $handler = new SaveHandler(
            $this->formTemplateRepository->reveal(),
            $this->templateDataFactory->reveal(),
            $this->templateValidatorTranslated->reveal(),
            $this->delayedEventDispatcher->reveal()
        );

        $handler->handle($save);
    }
}
