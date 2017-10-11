<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Query;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Planning\Formatter\FormattedPlanningView;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\ThirdParty\LENI\Query\LeniUserViewQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Query\LeniUserViewQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniPlanningDayView;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniPlanningView;
use Proximum\Vimeet\Application\ThirdParty\LENI\View\LeniUserView;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\Category\CategoryNameResolver;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;

class LeniUserViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $type = $this->prophesize(Type::class);
        $category = $this->prophesize(Category::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheets = [$sheet1->reveal(), $sheet2->reveal()];

        $event->getFallback()->willReturn('fr');
        $user->getId()->willReturn(12);
        $user->getEmail()->willReturn('email@email.fr');
        $user->getLocale()->willReturn('en');
        $event->getAvailableLocale('en')->willReturn('fr');
        $type->getId()->willReturn(64);
        $category->getId()->willReturn(67);

        // Mock
        $userInfoGuesser = $this->prophesize(UserInfoGuesser::class);
        $participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);
        $typeNameResolver = $this->prophesize(TypeNameResolver::class);
        $groupNameResolver = $this->prophesize(GroupNameResolver::class);
        $categoryNameResolver = $this->prophesize(CategoryNameResolver::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);

        $sheetInfoGuesser->guessSheetInfos($sheet1->reveal())->shouldBeCalled()->willReturn(['sheet_country' => 'FR']);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($sheets)
        ;

        $groupNameResolver
            ->resolve($event->reveal(), $user->reveal(), $sheets)
            ->shouldBeCalled()
            ->willReturn('sheetName')
        ;

        $typeNameResolver->resolveTypeWithPreloadedSheets($sheets)->shouldBeCalled()->willReturn($type->reveal());

        $categoryNameResolver
            ->resolveCategoryForPreloadSheets($sheets)
            ->shouldBeCalled()
            ->willReturn($category->reveal())
        ;

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
                'country' => ''
            ])
        ;

        $participantPlanningFormatter
            ->formatPlanningByDayFromUserAndEventWithUnallocated($user->reveal(), $event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(new FormattedPlanningView(['day1', 'day2'], 'unallocated'));

        $handler = new LeniUserViewQueryHandler(
            $userInfoGuesser->reveal(),
            $sheetInfoGuesser->reveal(),
            $participantPlanningFormatter->reveal(),
            $typeNameResolver->reveal(),
            $categoryNameResolver->reveal(),
            $groupNameResolver->reveal(),
            $sheetRepository->reveal()
        );
        $result = $handler->handle(new LeniUserViewQuery($event->reveal(), $user->reveal()));

        $unallocated = 'unallocated';
        $day1     = new LeniPlanningDayView('day1');
        $day2     = new LeniPlanningDayView('day2');
        $planning = new LeniPlanningView([$day1, $day2], $unallocated);
        $expected = new LeniUserView(
            12,
            'sheetName',
            64,
            67,
            'email@email.fr',
            'MME',
            'firstName',
            'lastName',
            'position',
            'phone',
            'mobile',
            'FR',
            'Inscrit',
            'en',
            $planning
        );

        $this->assertEquals($expected, $result);
    }
}
