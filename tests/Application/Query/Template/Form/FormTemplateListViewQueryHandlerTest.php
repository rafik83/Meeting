<?php

namespace Proximum\Vimeet\Tests\Application\Query\Template\Form;

use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateListViewQuery;
use Proximum\Vimeet\Application\Query\Template\Form\FormTemplateListViewQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\View\Template\Form\FormTemplateListView;
use Proximum\Vimeet\Application\View\Template\Form\FormTemplateView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Event\EventUrlGeneratorInterface;
use Proximum\Vimeet\Domain\Model\Template\FormTemplate;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Model\Type;

class FormTemplateListViewQueryHandlerTest extends TestCase
{
    public function test_handle(): void
    {
        $date = new \DateTime('2018-12-18 12:00:00.000');
        $event = $this->prophesize(Event::class);
        $event->getLocales()->shouldBeCalled()->willReturn(['fr']);

        $type = $this->prophesize(Type::class);

        $formTemplate = $this->prophesize(FormTemplate::class);
        $formTemplate->getId()->shouldBeCalled()->willReturn(1);
        $formTemplate->getTitle()->shouldBeCalled()->willReturn('Form FR');
        $formTemplate->getLocalizedTitle('fr')->shouldBeCalled()->willReturn('Form FR');
        $formTemplate->isPublished()->shouldBeCalled()->willReturn(true);
        $formTemplate->getTypes()->shouldBeCalled()->willReturn([$type->reveal()]);
        $formTemplate->getFallback()->shouldBeCalled()->willReturn('fr');
        $formTemplate->getCreatedAt()->shouldBeCalled()->willReturn($date);

        $formTemplateRepository = $this->prophesize(FormTemplateRepositoryInterface::class);
        $formTemplateRepository->findByEvent($event->reveal())
            ->shouldBeCalled()
            ->willReturn([
                $formTemplate->reveal(),
            ]);

        $eventUrlGenerator = $this->prophesize(EventUrlGeneratorInterface::class);
        $eventUrlGenerator->generateEventAbsoluteUrl($event->reveal(), 'event_show_form_template', ['formTemplate' => 1])
            ->shouldBeCalled()
            ->willReturn('http://mydomain/form/1');

        $handler = new FormTemplateListViewQueryHandler($formTemplateRepository->reveal(), $eventUrlGenerator->reveal());
        $result = $handler->handle(new FormTemplateListViewQuery($event->reveal()));

        $expectedResult = new FormTemplateListView([
            new FormTemplateView(1, 'Form FR', true, ['fr' => 'Form FR'], [$type->reveal()], 'http://mydomain/form/1', 'fr', $date)
        ]);

        $this->assertEquals($result, $expectedResult);
    }
}
