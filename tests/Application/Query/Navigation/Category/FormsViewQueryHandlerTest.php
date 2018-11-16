<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Navigation\Category;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Navigation\Category\FormsViewQuery;
use Proximum\Vimeet\Application\Query\Navigation\Category\FormsViewQueryHandler;
use Proximum\Vimeet\Application\View\Navigation\CategoryView;
use Proximum\Vimeet\Application\View\Navigation\LinkView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\Template\FormTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Template\Form\FormTemplateView;

class FormsViewQueryHandlerTest extends TestCase
{
    private $event;
    private $type;
    private $sheet;
    private $user;
    private $formTemplateRepository;
    private $formsViewQueryHandler;

    public function setUp()
    {
        $this->event = $this->prophesize(Event::class);
        $this->type = $this->prophesize(Type::class);

        $this->sheet = $this->prophesize(Sheet::class);
        $this->sheet->getType()->shouldBeCalled()->willReturn($this->type->reveal());

        $this->user = $this->prophesize(User::class);
        $this->formTemplateRepository = $this->prophesize(FormTemplateRepositoryInterface::class);
        $this->formsViewQueryHandler = new FormsViewQueryHandler($this->formTemplateRepository->reveal());
    }

    public function test_no_form_template_available_for_this_sheet_type()
    {
        $formsViewQuery = new FormsViewQuery($this->sheet->reveal(), $this->user->reveal(), 'fr');

        $this
            ->formTemplateRepository
            ->getFormTemplateViewByType($this->type->reveal(), 'fr')
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
            ->getFormTemplateViewByType($this->type->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([$logisticForm, $pollForm])
        ;

        $this->assertEquals(
            new CategoryView(
                'navigation.category.forms',
                'icon-Info_1',
                [
                    new LinkView('Logistique', '/url'),
                    new LinkView('Sondage', '/url'),
                ],
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
            ->getFormTemplateViewByType($this->type->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn([$logisticForm])
        ;

        $this->assertEquals(
            new CategoryView(
                'Informations complémentaires',
                'icon-Info_1',
                [
                    new LinkView('Logistique', '/url'),
                ],
                true
            ),
            $this->formsViewQueryHandler->handle($formsViewQuery)
        );
    }
}
