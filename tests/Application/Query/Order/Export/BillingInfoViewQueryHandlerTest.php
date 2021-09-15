<?php

namespace Proximum\Vimeet\Tests\Application\Query\Order\Export;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Proximum\Vimeet\Application\Adapter\IntlInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Order\Export\BillingInfoViewQuery;
use Proximum\Vimeet\Application\Query\Order\Export\BillingInfoViewQueryHandler;
use Proximum\Vimeet\Application\View\Order\Export\BillingInfoView;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Tests\Factory\EventFactory;

class BillingInfoViewQueryHandlerTest extends TestCase
{
    /** @var ObjectProphecy */
    private $billingInfoRepository;

    /** @var ObjectProphecy */
    private $translator;

    /** @var ObjectProphecy */
    private $intlAdapter;

    public function setUp()
    {
        $this->billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $this->translator            = $this->prophesize(TranslatorInterface::class);
        $this->intlAdapter           = $this->prophesize(IntlInterface::class);
    }

    public function testHandlePreload()
    {
        $event       = EventFactory::createEvent();
        $adminLocale = 'fr';

        $sheet  = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1);
        $sheet2->getId()->willReturn(2);

        $billingInfo = new BillingInfo($sheet->reveal());
        $billingInfo->update(
            'man',
            'lastName',
            'firstName',
            'position',
            'phone',
            'mobile',
            'email@email.fr',
            'company',
            new Address('street', 'zipCode', 'city', 'co'),
            'vatNumber',
            'thisIsTheReference'
        );
        $billingInfo2 = new BillingInfo($sheet2->reveal());

        $query = new BillingInfoViewQuery($sheet->reveal(), $adminLocale);

        $this->billingInfoRepository->findByEvent($event)->shouldBeCalled()->willReturn([$billingInfo, $billingInfo2]);
        $this->translator->trans('gender.man', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Man');
        $this->intlAdapter->getCountryName('co', $adminLocale)->shouldBeCalled()->willReturn('Country');

        $handler = new BillingInfoViewQueryHandler(
            $this->billingInfoRepository->reveal(),
            $this->translator->reveal(),
            $this->intlAdapter->reveal()
        );
        $handler->preload($event);
        $result = $handler->handle($query);

        $expected = new BillingInfoView(
            'Man',
            'lastName',
            'firstName',
            'position',
            'phone',
            'mobile',
            'email@email.fr',
            'company',
            'street',
            'zipCode',
            'city',
            'Country',
            'co',
            'vatNumber',
            'thisIsTheReference'
        );

        $this->assertEquals($expected, $result);
    }

    public function testHandleWithoutPreload()
    {
        $event = EventFactory::createEvent();
        $adminLocale = 'fr';

        $sheet  = $this->prophesize(Sheet::class);
        $sheet2 = $this->prophesize(Sheet::class);
        $sheet->getId()->willReturn(1);
        $sheet2->getId()->willReturn(2);

        $billingInfo = new BillingInfo($sheet->reveal());
        $billingInfo->update(
            'man',
            'lastName',
            'firstName',
            'position',
            'phone',
            'mobile',
            'email@email.fr',
            'company',
            new Address('street', 'zipCode', 'city', 'co'),
            'vatNumber',
            'thisIsTheReference'
        );

        $query = new BillingInfoViewQuery($sheet->reveal(), $adminLocale);

        $this->billingInfoRepository->getBySheet($sheet->reveal())->shouldBeCalled()->willReturn($billingInfo);
        $this->billingInfoRepository->findByEvent($event)->shouldNotBeCalled();
        $this->translator->trans('gender.man', [], 'export', $adminLocale)->shouldBeCalled()->willReturn('Man');
        $this->intlAdapter->getCountryName('co', $adminLocale)->shouldBeCalled()->willReturn('Country');

        $handler = new BillingInfoViewQueryHandler(
            $this->billingInfoRepository->reveal(),
            $this->translator->reveal(),
            $this->intlAdapter->reveal()
        );
        $result = $handler->handle($query);

        $expected = new BillingInfoView(
            'Man',
            'lastName',
            'firstName',
            'position',
            'phone',
            'mobile',
            'email@email.fr',
            'company',
            'street',
            'zipCode',
            'city',
            'Country',
            'co',
            'vatNumber',
            'thisIsTheReference'
        );

        $this->assertEquals($expected, $result);
    }
}
