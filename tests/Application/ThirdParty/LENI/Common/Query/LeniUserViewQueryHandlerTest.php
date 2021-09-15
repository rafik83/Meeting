<?php

namespace Proximum\Vimeet\Tests\Application\ThirdParty\LENI\Common\Query;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Components\Planning\Formatter\FormattedPlanningView;
use Proximum\Vimeet\Application\Components\Planning\Formatter\ParticipantPlanningFormatter;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserCustomDataQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserCustomDataQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserViewQuery;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\LeniUserViewQueryHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\PrepareLeaderData;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\Query\PrepareLeaderDataHandler;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeaderView;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeniPlanningDayView;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeniPlanningView;
use Proximum\Vimeet\Application\ThirdParty\LENI\Common\View\LeniUserView;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Package;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Model\User\Event\ExtraData;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\User\Event\ExtraDataRepositoryInterface;
use Proximum\Vimeet\Domain\Service\Category\CategoryNameResolver;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;
use Proximum\Vimeet\Domain\Sheet\HasRemainingToPay;

class LeniUserViewQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $type = $this->prophesize(Type::class);
        $category = $this->prophesize(Category::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->isEnabled()->willReturn(true);
        $sheet1->getUserLocale($user->reveal())->willReturn('en');
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet2->isEnabled()->willReturn(true);
        $sheets = [$sheet1->reveal(), $sheet2->reveal()];

        $event->getFallback()->willReturn('fr');
        $user->getId()->willReturn(12);
        $user->getEmail()->willReturn('email@email.fr');
        $user->getLocale()->willReturn('en');
        $event->getAvailableLocale('en')->willReturn('en');
        $type->getId()->willReturn(64);
        $category->getId()->willReturn(67);

        // Mock
        $userInfoGuesser = $this->prophesize(UserInfoGuesser::class);
        $participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);
        $typeNameResolver = $this->prophesize(TypeNameResolver::class);
        $groupNameResolver = $this->prophesize(GroupNameResolver::class);
        $categoryNameResolver = $this->prophesize(CategoryNameResolver::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $hasRemainingToPay = $this->prophesize(HasRemainingToPay::class);
        $prepareLeaderDatahandler = $this->prophesize(PrepareLeaderDataHandler::class);

        $hasRemainingToPay->isSatisfiedBy($sheet1->reveal())->shouldBeCalled()->willReturn(false);

        $sheetInfoGuesser->guessSheetInfos($sheet1->reveal())->shouldBeCalled()->willReturn(['sheet_country' => 'FR']);

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getAllSheetsByUserAndEvent($user->reveal(), $event->reveal())
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
            ->getUserInfoFromParticipant($user->reveal(), 'en', $sheets, false)
            ->shouldBeCalled()
            ->willReturn([
                'gender' => 'woman',
                'firstName' => 'firstName',
                'lastName' => 'lastName',
                'position' => 'position',
                'phone' => 'phone',
                'mobile' => 'mobile',
                'country' => '',
            ])
        ;

        $participantPlanningFormatter
            ->formatPlanningByDayFromUserAndEventWithUnallocated($user->reveal(), $event->reveal(), 'en')
            ->shouldBeCalled()
            ->willReturn(new FormattedPlanningView(['day1', 'day2'], 'unallocated'));

        $participant = $this->prophesize(Participant::class);
        $participantProduct = $this->prophesize(Product::class);
        $participantProduct->getId()->shouldBeCalled()->willReturn(1337);
        $participant->getParticipantProduct()->willReturn($participantProduct->reveal());
        $sheet1->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant->reveal());

        $prepareLeaderDatahandler
            ->handle(new PrepareLeaderData($sheet1->reveal(), $user->reveal()))
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $event->reveal(),
                'leni_user_id',
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $leniUserCustomDataQueryHandler = $this->prophesize(LeniUserCustomDataQueryHandler::class);
        $leniUserCustomDataQueryHandler
            ->handle(
                new LeniUserCustomDataQuery($event->reveal(), $user->reveal(), $type->reveal(), $sheet1->reveal(), 'en')
            )
            ->shouldBeCalled()
            ->willReturn(['ZL_PROFIL' => 'VISITEUR'])
        ;

        $handler = new LeniUserViewQueryHandler(
            $userInfoGuesser->reveal(),
            $sheetInfoGuesser->reveal(),
            $participantPlanningFormatter->reveal(),
            $typeNameResolver->reveal(),
            $categoryNameResolver->reveal(),
            $groupNameResolver->reveal(),
            $sheetRepository->reveal(),
            $hasRemainingToPay->reveal(),
            $prepareLeaderDatahandler->reveal(),
            $extraDataRepository->reveal(),
            $leniUserCustomDataQueryHandler->reveal()
        );
        $result = $handler->handle(new LeniUserViewQuery($event->reveal(), $user->reveal(), null));

        $expected = new LeniUserView(
            12,
            true,
            'sheetName',
            64,
            67,
            'email@email.fr',
            'woman',
            'firstName',
            'lastName',
            'position',
            'phone',
            'mobile',
            'FR',
            'en',
            null,
            new LeniPlanningView(
                [
                    new LeniPlanningDayView('day1'),
                    new LeniPlanningDayView('day2'),
                ],
                'unallocated'
            ),
            null,
            true,
            1337,
            ['ZL_PROFIL' => 'VISITEUR']
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithPreviousUserIdInFingerprint()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $type = $this->prophesize(Type::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->isEnabled()->willReturn(true);
        $sheet1->getUserLocale($user->reveal())->willReturn('en');
        $sheets = [$sheet1->reveal()];

        $event->getFallback()->willReturn('fr');
        $user->getId()->willReturn(12);
        $user->getEmail()->willReturn('email@email.fr');
        $user->getLocale()->willReturn('en');
        $event->getAvailableLocale('en')->willReturn('fr');
        $type->getId()->willReturn(64);

        // Mock
        $userInfoGuesser = $this->prophesize(UserInfoGuesser::class);
        $participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);
        $typeNameResolver = $this->prophesize(TypeNameResolver::class);
        $groupNameResolver = $this->prophesize(GroupNameResolver::class);
        $categoryNameResolver = $this->prophesize(CategoryNameResolver::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $hasRemainingToPay = $this->prophesize(HasRemainingToPay::class);
        $prepareLeaderDatahandler = $this->prophesize(PrepareLeaderDataHandler::class);

        $hasRemainingToPay->isSatisfiedBy($sheet1->reveal())->shouldBeCalled()->willReturn(true);

        $sheetInfoGuesser->guessSheetInfos($sheet1->reveal())->shouldNotBeCalled();

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getAllSheetsByUserAndEvent($user->reveal(), $event->reveal())
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
            ->willReturn(null)
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
                'country' => 'FR',
            ])
        ;

        $participantPlanningFormatter
            ->formatPlanningByDayFromUserAndEventWithUnallocated($user->reveal(), $event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(new FormattedPlanningView(['day1', 'day2'], 'unallocated'));

        $participant = $this->prophesize(Participant::class);
        $participant->getParticipantProduct()->willReturn(null);
        $sheet1->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant->reveal());

        $leaderView = new LeaderView('123-321', 'email@example.net', 'firstName', 'lastName', 'sheetName');
        $prepareLeaderDatahandler
            ->handle(new PrepareLeaderData($sheet1->reveal(), $user->reveal()))
            ->shouldBeCalled()
            ->willReturn($leaderView)
        ;

        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $event->reveal(),
                'leni_user_id',
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn(null)
        ;

        $leniUserCustomDataQueryHandler = $this->prophesize(LeniUserCustomDataQueryHandler::class);
        $leniUserCustomDataQueryHandler
            ->handle(
                new LeniUserCustomDataQuery($event->reveal(), $user->reveal(), $type->reveal(), $sheet1->reveal(), 'fr')
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $handler = new LeniUserViewQueryHandler(
            $userInfoGuesser->reveal(),
            $sheetInfoGuesser->reveal(),
            $participantPlanningFormatter->reveal(),
            $typeNameResolver->reveal(),
            $categoryNameResolver->reveal(),
            $groupNameResolver->reveal(),
            $sheetRepository->reveal(),
            $hasRemainingToPay->reveal(),
            $prepareLeaderDatahandler->reveal(),
            $extraDataRepository->reveal(),
            $leniUserCustomDataQueryHandler->reveal()
        );

        $extraData = new ExtraData(
            $user->reveal(),
            $event->reveal(),
            'leni_fingerprint',
            'a:2:{s:4:"Cab2";s:1:"6";s:2:"Id";s:36:"f93a5b28-12b0-e711-80e1-0cc47a02bf5b";}',
            new \DateTime()
        );

        $result = $handler->handle(new LeniUserViewQuery($event->reveal(), $user->reveal(), $extraData));

        $expected = new LeniUserView(
            12,
            true,
            'sheetName',
            64,
            null,
            'email@email.fr',
            'woman',
            'firstName',
            'lastName',
            'position',
            'phone',
            'mobile',
            'FR',
            'en',
            $leaderView,
            new LeniPlanningView(
                [
                    new LeniPlanningDayView('day1'),
                    new LeniPlanningDayView('day2'),
                ],
                'unallocated'
            ),
            'f93a5b28-12b0-e711-80e1-0cc47a02bf5b',
            false,
            null,
            []
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithPreviousUserIdInExtraData()
    {
        $event = $this->prophesize(Event::class);
        $user = $this->prophesize(User::class);

        $type = $this->prophesize(Type::class);
        $sheet1 = $this->prophesize(Sheet::class);
        $sheet1->isEnabled()->willReturn(true);
        $sheet1->getUserLocale($user->reveal())->willReturn('en');
        $sheets = [$sheet1->reveal()];

        $event->getFallback()->willReturn('fr');
        $user->getId()->willReturn(12);
        $user->getEmail()->willReturn('email@email.fr');
        $user->getLocale()->willReturn('en');
        $event->getAvailableLocale('en')->willReturn('fr');
        $type->getId()->willReturn(64);

        // Mock
        $userInfoGuesser = $this->prophesize(UserInfoGuesser::class);
        $participantPlanningFormatter = $this->prophesize(ParticipantPlanningFormatter::class);
        $typeNameResolver = $this->prophesize(TypeNameResolver::class);
        $groupNameResolver = $this->prophesize(GroupNameResolver::class);
        $categoryNameResolver = $this->prophesize(CategoryNameResolver::class);
        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $hasRemainingToPay = $this->prophesize(HasRemainingToPay::class);
        $prepareLeaderDatahandler = $this->prophesize(PrepareLeaderDataHandler::class);

        $hasRemainingToPay->isSatisfiedBy($sheet1->reveal())->shouldBeCalled()->willReturn(true);

        $sheetInfoGuesser->guessSheetInfos($sheet1->reveal())->shouldNotBeCalled();

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getAllSheetsByUserAndEvent($user->reveal(), $event->reveal())
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
            ->willReturn(null)
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
                'country' => 'FR',
            ])
        ;

        $participantPlanningFormatter
            ->formatPlanningByDayFromUserAndEventWithUnallocated($user->reveal(), $event->reveal(), 'fr')
            ->shouldBeCalled()
            ->willReturn(new FormattedPlanningView(['day1', 'day2'], 'unallocated'));

        $participant = $this->prophesize(Participant::class);
        $participant->getParticipantProduct()->willReturn(null);
        $sheet1->getUserParticipant($user->reveal())->shouldBeCalled()->willReturn($participant->reveal());

        $leaderView = new LeaderView('123-321', 'email@example.net', 'firstName', 'lastName', 'sheetName');
        $prepareLeaderDatahandler
            ->handle(new PrepareLeaderData($sheet1->reveal(), $user->reveal()))
            ->shouldBeCalled()
            ->willReturn($leaderView)
        ;

        $leniUserIdExtraData = $this->prophesize(ExtraData::class);
        $leniUserIdExtraData->getValue()->willReturn('leni-saved-user-id');
        $extraDataRepository = $this->prophesize(ExtraDataRepositoryInterface::class);
        $extraDataRepository
            ->getExtraDataForEventNameAndUser(
                $event->reveal(),
                'leni_user_id',
                $user->reveal()
            )
            ->shouldBeCalled()
            ->willReturn($leniUserIdExtraData->reveal())
        ;

        $leniUserCustomDataQueryHandler = $this->prophesize(LeniUserCustomDataQueryHandler::class);
        $leniUserCustomDataQueryHandler
            ->handle(
                new LeniUserCustomDataQuery($event->reveal(), $user->reveal(), $type->reveal(), $sheet1->reveal(), 'fr')
            )
            ->shouldBeCalled()
            ->willReturn([])
        ;

        $handler = new LeniUserViewQueryHandler(
            $userInfoGuesser->reveal(),
            $sheetInfoGuesser->reveal(),
            $participantPlanningFormatter->reveal(),
            $typeNameResolver->reveal(),
            $categoryNameResolver->reveal(),
            $groupNameResolver->reveal(),
            $sheetRepository->reveal(),
            $hasRemainingToPay->reveal(),
            $prepareLeaderDatahandler->reveal(),
            $extraDataRepository->reveal(),
            $leniUserCustomDataQueryHandler->reveal()
        );

        $result = $handler->handle(new LeniUserViewQuery($event->reveal(), $user->reveal(), null));

        $expected = new LeniUserView(
            12,
            true,
            'sheetName',
            64,
            null,
            'email@email.fr',
            'woman',
            'firstName',
            'lastName',
            'position',
            'phone',
            'mobile',
            'FR',
            'en',
            $leaderView,
            new LeniPlanningView(
                [
                    new LeniPlanningDayView('day1'),
                    new LeniPlanningDayView('day2'),
                ],
                'unallocated'
            ),
            'leni-saved-user-id',
            false,
            null,
            []
        );

        $this->assertEquals($expected, $result);
    }
}
