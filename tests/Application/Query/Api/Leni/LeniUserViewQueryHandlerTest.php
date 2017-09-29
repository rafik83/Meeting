<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\Query\Api\Leni;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Planning\Formatter\FormattedPlanningView;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\Query\Api\Leni\LeniUserViewQuery;
use Proximum\Vimeet\Application\Query\Api\Leni\LeniUserViewQueryHandler;
use Proximum\Vimeet\Application\View\Api\Leni\LeniPlanningDayView;
use Proximum\Vimeet\Application\View\Api\Leni\LeniPlanningView;
use Proximum\Vimeet\Application\View\Api\Leni\LeniUserView;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Service\Category\CategoryNameResolver;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;

class LeniUserViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheets = [$sheet1->reveal(), $sheet2->reveal()];

        $event->getFallback()->willReturn('fr');
        $user->getId()->willReturn(12);
        $user->getEmail()->willReturn('email@email.fr');
        $user->getLocale()->willReturn('en');
        $event->getAvailableLocale('en')->willReturn('fr');

        // Mock
        $userInfoGuesser = $this->prophesize(UserInfoGuesser::class);
        $participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);
        $typeNameResolver = $this->prophesize(TypeNameResolver::class);
        $groupNameResolver = $this->prophesize(GroupNameResolver::class);
        $categoryNameResolver = $this->prophesize(CategoryNameResolver::class);

        $groupNameResolver->resolve($event->reveal(), $user->reveal(), $sheets)->shouldBeCalled()->willReturn('sheetName');
        $typeNameResolver->resolveWithPreloadedSheets($sheets, 'fr')->shouldBeCalled()->willReturn('typeName');
        $categoryNameResolver->resolveForPreloadSheets($sheets, 'fr')->shouldBeCalled()->willReturn('categoryName');
        $userInfoGuesser
            ->getUserInfoFromParticipant($user->reveal(), 'fr', $sheets, false)
            ->shouldBeCalled()
            ->willReturn([
                'gender' => 'woman',
                'firstName' => 'firstName',
                'lastName' => 'lastName',
                'position' => 'position',
                'phone' => 'phone',
                'mobile' => 'mobile',
            ])
        ;

        $participantPlanningFormatter
            ->formatPlanningByDayFromUserAndEventWithUnallocated($user->reveal(), $event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(new FormattedPlanningView(['day1', 'day2'], 'unallocated'));

        $handler = new LeniUserViewQueryHandler(
            $userInfoGuesser->reveal(),
            $participantPlanningFormatter->reveal(),
            $typeNameResolver->reveal(),
            $categoryNameResolver->reveal(),
            $groupNameResolver->reveal()
        );
        $result = $handler->handle(new LeniUserViewQuery($event->reveal(), $user->reveal(), $sheets));

        $unallocated = 'unallocated';
        $day1     = new LeniPlanningDayView('day1');
        $day2     = new LeniPlanningDayView('day2');
        $planning = new LeniPlanningView([$day1, $day2], $unallocated);
        $expected = new LeniUserView(
            12,
            'sheetName',
            'typeName',
            'categoryName',
            'email@email.fr',
            'woman',
            'firstName',
            'lastName',
            'position',
            'phone',
            'mobile',
            $planning
        );

        $this->assertEquals($expected, $result);
    }
}
