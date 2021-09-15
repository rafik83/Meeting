<?php

namespace Proximum\Vimeet\Tests\Application\Query\Badge;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Application\Adapter\QRCodeGeneratorInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Query\Badge\GetBadgeConfigurationByTypeQuery;
use Proximum\Vimeet\Application\Query\Badge\GetExampleBadgeByEventQuery;
use Proximum\Vimeet\Application\Query\Badge\GetExampleBadgeByEventQueryHandler;
use Proximum\Vimeet\Application\Query\Badge\UserBadgeByEventView;
use Proximum\Vimeet\Domain\Model\Badge;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Type;

class GetExampleBadgeByEventQueryHandlerTest extends TestCase
{
    public function testHandle(): void
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
        $badge->isMirrored()->shouldBeCalled()->willReturn(true);
        $badge->getLeftImage()->shouldBeCalled()->willReturn(null);
        $badge->getRightImage()->shouldBeCalled()->willReturn(null);
        $badge->isRightImageFullHeight()->shouldBeCalled()->willReturn(false);

        $queryBus = $this->prophesize(QueryBusInterface::class);
        $queryBus
            ->handle(new GetBadgeConfigurationByTypeQuery($type->reveal()))
            ->shouldBeCalled()
            ->willReturn($badge->reveal())
        ;

        $qrCodeGenerator = $this->prophesize(QRCodeGeneratorInterface::class);
        $qrCodeGenerator
            ->generateBase64Image('123456789012')
            ->shouldBeCalled()
            ->willReturn('data:qrCodeImageBase64')
        ;

        $expectedUserBadgeByEventView = new UserBadgeByEventView(
            'Hamilton Technologies',
            'Margaret',
            'Hamilton',
            null,
            'Catégorie d\'exemple',
            '123456789012',
            'data:qrCodeImageBase64',
            '/path/to/header.png',
            '#ffffff',
            '#000000',
            'Etats-Unis',
            true,
            null,
            null,
            false,
            '#eee',
            '#000'
        );

        $getUserBadgeByEventQueryHandler = new GetExampleBadgeByEventQueryHandler(
            $queryBus->reveal(),
            $qrCodeGenerator->reveal()
        );
        $result = $getUserBadgeByEventQueryHandler->handle(
            new GetExampleBadgeByEventQuery($event->reveal(), $type->reveal())
        );

        $this->assertEquals($expectedUserBadgeByEventView, $result);
    }
}
