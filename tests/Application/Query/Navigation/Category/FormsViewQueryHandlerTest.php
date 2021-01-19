<?php

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Category;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Query\Navigation\Category\FormsViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\FormsViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Form\FormTemplateView;

class FormsViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $event;

    /** @var ObjectProphecy */
    private $type;

    /** @var ObjectProphecy */
    private $sheet;

    /** @var ObjectProphecy */
    private $user;

    /** @var ObjectProphecy */
    private $formTemplateRepository;

    /** @var ObjectProphecy */
    private $router;

    /** @var FormsViewQueryHandler */
    private $formsViewQueryHandler;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getId()->willReturn(1337);
        $this->sheet->getType()->shouldBeCalled()->willReturn($this->type->reveal());

        $this->user = $this->prophesize(User::class);

        $this->formTemplateRepository = $this->prophesize(FormTemplateRepositoryInterface::class);
        $this->router = $this->prophesize(RouterInterface::class);

        $this->formsViewQueryHandler = new FormsViewQueryHandler(
            $this->formTemplateRepository->reveal(),
            $this->router->reveal()
        );
    }

    public function test_no_form_template_available_for_this_sheet_type()
    {
        $formsViewQuery = new FormsViewQuery($this->sheet->reveal(), $this->user->reveal(), 'fr');

        $this
            ->formTemplateRepository
            ->getPublishedFormTemplateViewByType($this->type->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $this->assertNull($this->formsViewQueryHandler->handle($formsViewQuery));
    }

    public function test_forms_template_available_for_this_sheet_type_but_there_is_no_static_formulation()
    {
        $formsViewQuery = new FormsViewQuery($this->sheet->reveal(), $this->user->reveal(), 'fr');

        $logisticForm = new FormTemplateView(1, 'Logistique');
        $pollForm = new FormTemplateView(2, 'Sondage');

        $this
            ->formTemplateRepository
            ->getPublishedFormTemplateViewByType($this->type->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([$logisticForm, $pollForm])
        ;

        $participant = $this->prophesize(Participant::class);
        $participant->getId()->shouldBeCalled()->willReturn(42);

        $this
            ->sheet
            ->getUserParticipant($this->user->reveal())
            ->shouldBeCalled()
            ->willReturn($participant->reveal())
        ;

        $this
            ->router
            ->generate(
                'event_participant_fill_form',
                [
                    'sheet' => 1337,
                    'participant' => 42,
                    'formTemplate' => 1,
                    'step' => 1,
                ]
            )
            ->shouldBeCalled()
            ->willReturn('/url/to/form/1')
        ;

        $this
            ->router
            ->generate(
                'event_participant_fill_form',
                [
                    'sheet' => 1337,
                    'participant' => 42,
                    'formTemplate' => 2,
                    'step' => 1,
                ]
            )
            ->shouldBeCalled()
            ->willReturn('/url/to/form/2')
        ;

        $this->assertEquals(
            new CategoryView(
                'navigation.category.forms',
                'icon-Info_1',
                [
                    new LinkView('Logistique', '/url/to/form/1'),
                    new LinkView('Sondage', '/url/to/form/2'),
                ],
                true,
                true
            ),
            $this->formsViewQueryHandler->handle($formsViewQuery)
        );
    }

    public function test_form_template_available_for_this_sheet_type_and_there_is_a_custom_static_formulation()
    {
        $staticFormulation = new StaticFormulation(
            $this->event->reveal(),
            'navigation.category.forms',
            [$this->type->reveal()]
        );
        $staticFormulation->translate('fr', 'Informations complémentaires');

        $formsViewQuery = new FormsViewQuery(
            $this->sheet->reveal(),
            $this->user->reveal(),
            'fr',
            $staticFormulation
        );

        $logisticForm = new FormTemplateView(1, 'Logistique');

        $this
            ->formTemplateRepository
            ->getPublishedFormTemplateViewByType($this->type->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([$logisticForm])
        ;

        $participant = $this->prophesize(Participant::class);
        $participant->getId()->shouldBeCalled()->willReturn(42);

        $this
            ->sheet
            ->getUserParticipant($this->user->reveal())
            ->shouldBeCalled()
            ->willReturn(null)
        ;
        $this
            ->sheet
            ->getFirstParticipant()
            ->shouldBeCalled()
            ->willReturn($participant->reveal())
        ;

        $this
            ->router
            ->generate(
                'event_participant_fill_form',
                [
                    'sheet' => 1337,
                    'participant' => 42,
                    'formTemplate' => 1,
                    'step' => 1,
                ]
            )
            ->shouldBeCalled()
            ->willReturn('/url/to/form/1')
        ;

        $this->assertEquals(
            new CategoryView(
                'Informations complémentaires',
                'icon-Info_1',
                [
                    new LinkView('Logistique', '/url/to/form/1'),
                ],
                true,
                true
            ),
            $this->formsViewQueryHandler->handle($formsViewQuery)
        );
    }
}
