<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\StaticFormulation;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\StaticFormulation\Customized\CustomizedStaticFormulationViewQuery;
use Proximum\Vimeet\Application\Query\StaticFormulation\Customized\CustomizedStaticFormulationViewQueryHandler;
use Proximum\Vimeet\Application\Query\StaticFormulation\StaticFormulationListViewQuery;
use Proximum\Vimeet\Application\Query\StaticFormulation\StaticFormulationListViewQueryHandler;
use Proximum\Vimeet\Application\View\StaticFormulation\Customized\CustomizedStaticFormulationView;
use Proximum\Vimeet\Application\View\StaticFormulation\Generic\GenericStaticFormulationView;
use Proximum\Vimeet\Application\View\StaticFormulation\StaticFormulationListView;
use Proximum\Vimeet\Application\View\StaticFormulation\StaticFormulationView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\StaticFormulation;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Repository\StaticFormulation\StaticFormulationRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\TypeRepositoryInterface;
use Proximum\Vimeet\Domain\StaticFormulation\Constant;

class StaticFormulationListViewQueryHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $key = Constant::STATIC_FORMULATION_KEY_SHEET;
        $event = $this->prophesize(Event::class);
        $event->getAvailableLocale('en')->shouldBeCalled()->willReturn('fr');
        $type1 = $this->prophesize(Type::class);
        $type2 = $this->prophesize(Type::class);
        $types = [
            $type1->reveal(),
            $type2->reveal(),
        ];
        $type1->getId()->shouldBeCalled()->willReturn(111);
        $type2->getId()->shouldBeCalled()->willReturn(112);
        $type1->getTitle('fr')->shouldBeCalled()->willReturn('type 1');
        $type2->getTitle('fr')->shouldBeCalled()->willReturn('type 2');

        $typeRepository = $this->prophesize(TypeRepositoryInterface::class);
        $typeRepository->getTypesByEvent($event->reveal())->shouldBeCalled()->willReturn($types);

        $staticFormulationRepository = $this->prophesize(StaticFormulationRepositoryInterface::class);

        $staticFormulation1 = $this->prophesize(StaticFormulation::class);
        $staticFormulation2 = $this->prophesize(StaticFormulation::class);
        $staticFormulation1->getKey()->shouldBeCalled()->willReturn($key);
        $staticFormulation2->getKey()->shouldBeCalled()->willReturn($key);

        $staticFormulations = [
            $staticFormulation1->reveal(),
            $staticFormulation2->reveal(),
        ];
        $staticFormulationRepository->findByEvent($event->reveal())->shouldBeCalled()->willReturn($staticFormulations);

        $customizedView1 = new CustomizedStaticFormulationView($key, 11, 'title 1', [111 => 'type 1']);
        $customizedView2 = new CustomizedStaticFormulationView($key, 12, 'title 2', [112 => 'type 2']);

        $customizedHandler = $this->prophesize(CustomizedStaticFormulationViewQueryHandler::class);
        $customizedHandler->handle(new CustomizedStaticFormulationViewQuery($staticFormulation1->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($customizedView1)
        ;
        $customizedHandler->handle(new CustomizedStaticFormulationViewQuery($staticFormulation2->reveal(), 'fr'))
            ->shouldBeCalled()
            ->willReturn($customizedView2)
        ;

        $translator = $this->prophesize(TranslatorInterface::class);
        $translator
            ->trans(
                Constant::STATIC_FORMULATION_LIST[Constant::STATIC_FORMULATION_KEY_SHEET]['label'],
                [],
                'messages',
                'fr'
            )->shouldBeCalled()
            ->willReturn('sheet')
        ;
        $translator
            ->trans(
                Constant::STATIC_FORMULATION_LIST[Constant::STATIC_FORMULATION_KEY_CATALOG]['label'],
                [],
                'messages',
                'fr'
            )->shouldBeCalled()
            ->willReturn('catalog')
        ;
        $translator
            ->trans(
                Constant::STATIC_FORMULATION_LIST[Constant::STATIC_FORMULATION_KEY_MEETING_REQUEST]['label'],
                [],
                'messages',
                'fr'
            )->shouldBeCalled()
            ->willReturn('meeting request')
        ;
        $translator
            ->trans(
                Constant::STATIC_FORMULATION_LIST[Constant::STATIC_FORMULATION_KEY_PACKAGE]['label'],
                [],
                'messages',
                'fr'
            )->shouldBeCalled()
            ->willReturn('package')
        ;
        $translator
            ->trans(
                Constant::STATIC_FORMULATION_LIST[Constant::STATIC_FORMULATION_KEY_AGENDA]['label'],
                [],
                'messages',
                'fr'
            )->shouldBeCalled()
            ->willReturn('agenda')
        ;
        $translator
            ->trans(
                Constant::STATIC_FORMULATION_LIST[Constant::STATIC_FORMULATION_KEY_PROGRAM]['label'],
                [],
                'messages',
                'fr'
            )->shouldBeCalled()
            ->willReturn('program')
        ;
        $translator
            ->trans(
                Constant::STATIC_FORMULATION_LIST[Constant::STATIC_FORMULATION_KEY_BILLING]['label'],
                [],
                'messages',
                'fr'
            )->shouldBeCalled()
            ->willReturn('billing')
        ;
        $translator
            ->trans(
                Constant::STATIC_FORMULATION_LIST[Constant::STATIC_FORMULATION_KEY_BADGE]['label'],
                [],
                'messages',
                'fr'
            )->shouldBeCalled()
            ->willReturn('badge')
        ;
        $translator
            ->trans(
                Constant::STATIC_FORMULATION_LIST[Constant::STATIC_FORMULATION_KEY_MEMBER_SPACE]['label'],
                [],
                'messages',
                'fr'
            )->shouldBeCalled()
            ->willReturn('member space')
        ;
        $translator
            ->trans(
                Constant::STATIC_FORMULATION_LIST[Constant::STATIC_FORMULATION_KEY_FORMS]['label'],
                [],
                'messages',
                'fr'
            )->shouldBeCalled()
            ->willReturn('My venue')
        ;

        $translator
            ->trans(
                Constant::STATIC_FORMULATION_LIST[Constant::STATIC_FORMULATION_KEY_VISIO_TEST]['label'],
                [],
                'messages',
                'fr'
            )->shouldBeCalled()
            ->willReturn('Visio test')
        ;

        $query = new StaticFormulationListViewQuery($event->reveal(), 'en');
        $handler = new StaticFormulationListViewQueryHandler(
            $typeRepository->reveal(),
            $staticFormulationRepository->reveal(),
            $translator->reveal(),
            $customizedHandler->reveal()
        );

        $result = $handler->handle($query);

        $view1 = new StaticFormulationView(
            Constant::STATIC_FORMULATION_KEY_SHEET,
            new GenericStaticFormulationView(
                Constant::STATIC_FORMULATION_KEY_SHEET,
                'sheet',
                []
            ),
            [
                $customizedView1,
                $customizedView2,
            ]
        );
        $view2 = new StaticFormulationView(
            Constant::STATIC_FORMULATION_KEY_CATALOG,
            new GenericStaticFormulationView(
                Constant::STATIC_FORMULATION_KEY_CATALOG,
                'catalog',
                [
                    'type 1',
                    'type 2',
                ]
            ),
            []
        );
        $view3 = new StaticFormulationView(
            Constant::STATIC_FORMULATION_KEY_MEETING_REQUEST,
            new GenericStaticFormulationView(
                Constant::STATIC_FORMULATION_KEY_MEETING_REQUEST,
                'meeting request',
                [
                    'type 1',
                    'type 2',
                ]
            ),
            []
        );
        $view4 = new StaticFormulationView(
            Constant::STATIC_FORMULATION_KEY_PACKAGE,
            new GenericStaticFormulationView(
                Constant::STATIC_FORMULATION_KEY_PACKAGE,
                'package',
                [
                    'type 1',
                    'type 2',
                ]
            ),
            []
        );
        $view5 = new StaticFormulationView(
            Constant::STATIC_FORMULATION_KEY_AGENDA,
            new GenericStaticFormulationView(
                Constant::STATIC_FORMULATION_KEY_AGENDA,
                'agenda',
                [
                    'type 1',
                    'type 2',
                ]
            ),
            []
        );
        $view6 = new StaticFormulationView(
            Constant::STATIC_FORMULATION_KEY_PROGRAM,
            new GenericStaticFormulationView(
                Constant::STATIC_FORMULATION_KEY_PROGRAM,
                'program',
                [
                    'type 1',
                    'type 2',
                ]
            ),
            []
        );
        $view7 = new StaticFormulationView(
            Constant::STATIC_FORMULATION_KEY_BILLING,
            new GenericStaticFormulationView(
                Constant::STATIC_FORMULATION_KEY_BILLING,
                'billing',
                [
                    'type 1',
                    'type 2',
                ]
            ),
            []
        );
        $view8 = new StaticFormulationView(
            Constant::STATIC_FORMULATION_KEY_MEMBER_SPACE,
            new GenericStaticFormulationView(
                Constant::STATIC_FORMULATION_KEY_MEMBER_SPACE,
                'member space',
                [
                    'type 1',
                    'type 2',
                ]
            ),
            []
        );
        $view9 = new StaticFormulationView(
            Constant::STATIC_FORMULATION_KEY_BADGE,
            new GenericStaticFormulationView(
                Constant::STATIC_FORMULATION_KEY_BADGE,
                'badge',
                [
                    'type 1',
                    'type 2',
                ]
            ),
            []
        );
        $view10 = new StaticFormulationView(
            Constant::STATIC_FORMULATION_KEY_FORMS,
            new GenericStaticFormulationView(
                Constant::STATIC_FORMULATION_KEY_FORMS,
                'My venue',
                [
                    'type 1',
                    'type 2',
                ]
            ),
            []
        );
        $view11 = new StaticFormulationView(
            Constant::STATIC_FORMULATION_KEY_VISIO_TEST,
            new GenericStaticFormulationView(
                Constant::STATIC_FORMULATION_KEY_VISIO_TEST,
                'Visio test',
                [
                    'type 1',
                    'type 2',
                ]
            ),
            []
        );
        $views = [
            $view1,
            $view2,
            $view3,
            $view4,
            $view5,
            $view6,
            $view7,
            $view8,
            $view9,
            $view10,
            $view11,
        ];

        $expected = new StaticFormulationListView($views);

        $this->assertEquals($expected, $result);
    }
}
