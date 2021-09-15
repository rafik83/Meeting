<?php

namespace Proximum\Vimeet\Tests\Application\Query\Badge;

use Proximum\Vimeet\Application\Adapter\QRCodeGeneratorInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\Components\User\UserInfoGuesser;
use Proximum\Vimeet\Application\Query\Badge\GetBadgeConfigurationByTypeQuery;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQuery;
use Proximum\Vimeet\Application\Query\Badge\GetUserBadgeByEventQueryHandler;
use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Query\Badge\QRCode\QRCodeIdentifierQuery;
use Proximum\Vimeet\Application\Query\Badge\UserBadgeByEventView;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Category;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Type;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Repository\SheetRepositoryInterface;
use Proximum\Vimeet\Domain\Service\Category\CategoryNameResolver;
use Proximum\Vimeet\Domain\Service\SheetsGroup\GroupNameResolver;
use Proximum\Vimeet\Domain\Service\Type\TypeNameResolver;
use Proximum\Vimeet\Domain\User\Sheet\FirstParticipantSheetOfUserGetter;
use Proximum\Vimeet\Infrastructure\Adapter\IntlAdapter;

class GetUserBadgeByEventQueryHandlerTest extends TestCase
{
    public function testHandle()
    {
        $event = $this->prophesize(Event::class);
        $event->getFallback()->shouldBeCalled()->willReturn('en');
        $event->getLocalizedMobileLogo('en')->shouldBeCalled()->willReturn('/path/to/header.png');

        $configuration = new Event\Configuration();
        $configuration->setColors('#fff', '#fff', '#fff', '#eee', '#000', '#fff', '#fff', '#fff', '#fff');
        $event->getConfiguration()->shouldBeCalled()->willReturn($configuration);

        $type = $this->prophesize(Type::class);
        $type->getTitle('en')->shouldNotBeCalled();

        $badge = $this->prophesize(Badge::class);
        $badge->isActivated()->shouldBeCalled()->willReturn(true);
        $badge->isShowQRCode()->shouldBeCalled()->willReturn(true);
        $badge->isShowHeader()->shouldBeCalled()->willReturn(true);
        $badge->getHeader()->shouldBeCalled()->willReturn(null);
        $badge->isShowFirstName()->shouldBeCalled()->willReturn(true);
        $badge->isShowLastName()->shouldBeCalled()->willReturn(true);
        $badge->isShowPosition()->shouldBeCalled()->willReturn(false);
        $badge->isShowSheetTitle()->shouldBeCalled()->willReturn(true);
        $badge->getFooterColor()->shouldBeCalled()->willReturn('#000000');
        $badge->getFooterTextColor()->shouldBeCalled()->willReturn('#ffffff');
        $badge->isShowFooterTypeOrCategory()->shouldBeCalled()->willReturn(true);
        $badge->isShowFooterType()->shouldBeCalled()->willReturn(false);
        $badge->isShowCountry()->shouldBeCalled()->willReturn(true);
        $badge->getEvent()->shouldBeCalled()->willReturn($event->reveal());
        $badge->isMirrored()->shouldBeCalled()->willReturn(false);
        $badge->getLeftImage()->shouldBeCalled()->willReturn(null);
        $badge->getRightImage()->shouldBeCalled()->willReturn(null);
        $badge->isRightImageFullHeight()->shouldBeCalled()->willReturn(false);

        $category = $this->prophesize(Category::class);
        $category->getTitle('en')->shouldBeCalled()->willReturn('Exhibitor');

        $sheet = $this->prophesize(Sheet::class);
        $sheets = [$sheet->reveal()];

        $user = $this->prophesize(User::class);

        $queryBus = $this->prophesize(QueryBusInterface::class);
        $queryBus
            ->handle(new QRCodeIdentifierQuery($event->reveal(), $user->reveal()))
            ->shouldBeCalled()
            ->willReturn('0000133700042')
        ;
        $queryBus
            ->handle(new GetBadgeConfigurationByTypeQuery($type->reveal()))
            ->shouldBeCalled()
            ->willReturn($badge->reveal())
        ;

        $qrCodeGenerator = $this->prophesize(QRCodeGeneratorInterface::class);
        $qrCodeGenerator
            ->generateBase64Image('0000133700042')
            ->shouldBeCalled()
            ->willReturn('data:qrCodeImageBase64')
        ;

        $sheetRepository = $this->prophesize(SheetRepositoryInterface::class);
        $sheetRepository
            ->getSheetsByUserAndEvent($user->reveal(), $event->reveal())
            ->shouldBeCalled()
            ->willReturn($sheets)
        ;

        $groupNameResolver = $this->prophesize(GroupNameResolver::class);
        $groupNameResolver
            ->resolve($event->reveal(), $user->reveal(), $sheets)
            ->shouldBeCalled()
            ->willReturn('Taxi company')
        ;

        $typeNameResolver = $this->prophesize(TypeNameResolver::class);
        $typeNameResolver
            ->resolveTypeWithPreloadedSheets($sheets)
            ->shouldBeCalled()
            ->willReturn($type->reveal())
        ;

        $categoryNameResolver = $this->prophesize(CategoryNameResolver::class);
        $categoryNameResolver
            ->resolveCategoryForPreloadSheets($sheets)
            ->shouldBeCalled()
            ->willReturn($category->reveal())
        ;

        $userInfoGuesser = $this->prophesize(UserInfoGuesser::class);
        $userInfoGuesser
            ->getUserInfoFromParticipant($user->reveal(), 'en', $sheets)
            ->shouldBeCalled()
            ->willReturn(['firstName' => 'Korben', 'lastName' => 'Dallas', 'position' => 'Taxi driver'])
        ;

        $firstParticipantSheetOfUserGetter = $this->prophesize(FirstParticipantSheetOfUserGetter::class);
        $firstParticipantSheetOfUserGetter->getFirstParticipantSheet($user->reveal(), $sheets)
            ->shouldBeCalled()
            ->willReturn($sheet->reveal());

        $sheetInfoGuesser = $this->prophesize(SheetInfoGuesser::class);
        $sheetInfoGuesser
            ->guessSheetInfos($sheet->reveal())
            ->shouldBeCalled()
            ->willReturn([
                'sheet_country' => 'FR'
            ])
        ;

        $intlAdapter = $this->prophesize(IntlAdapter::class);
        $intlAdapter->getCountryName('FR')->shouldBeCalled()->willReturn('france');

        $expectedUserBadgeByEventView = new UserBadgeByEventView(
            'Taxi company',
            'Korben',
            'Dallas',
            null,
            'Exhibitor',
            '0000133700042',
            'data:qrCodeImageBase64',
            '/path/to/header.png',
            '#ffffff',
            '#000000',
            'france',
            false,
            null,
            null,
            false,
            '#eee',
            '#000'
        );

        $getUserBadgeByEventQueryHandler = new GetUserBadgeByEventQueryHandler(
            $queryBus->reveal(),
            $qrCodeGenerator->reveal(),
            $sheetRepository->reveal(),
            $groupNameResolver->reveal(),
            $categoryNameResolver->reveal(),
            $typeNameResolver->reveal(),
            $userInfoGuesser->reveal(),
            $sheetInfoGuesser->reveal(),
            $firstParticipantSheetOfUserGetter->reveal(),
            $intlAdapter->reveal()
        );
        $result = $getUserBadgeByEventQueryHandler->handle(new GetUserBadgeByEventQuery($event->reveal(), $user->reveal()));

        $this->assertEquals($expectedUserBadgeByEventView, $result);
    }
}
