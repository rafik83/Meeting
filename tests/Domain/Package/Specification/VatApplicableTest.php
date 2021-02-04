<?php

namespace Proximum\Vimeet\Tests\Domain\Package\Specification;

use PHPUnit\Framework\TestCase;
use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Package\Specification\VatApplicable;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class QuantityMaxGuesserTest extends TestCase
{


    public function getEventCountryAndBillingCountry(): array
    {
        return [["mc", "fr"], ["fr", "mc"], ["fr", "fr"]];
    }

    /**
     * @dataProvider getEventCountryAndBillingCountry()
     */

    public function testShouldReturnTrueIfVATIsApplicable(string $eventCountry, string $billingCountry): void
    {

        $europeanCountries = ["fr", "be"];
        $billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $vatApplicable = new VatApplicable($billingInfoRepository->reveal(), $europeanCountries);

        $mode = "DummyMode";
        $vatNumber = "VAT 666";

        $this->assertEquals($vatApplicable->isApplicable($mode, $eventCountry, $billingCountry, $vatNumber), true);
    }


    /**
     * @dataProvider getEventCountryAndBillingCountry()
     */

    public function testShouldReturnFalseIfVATINotsApplicable(): void
    {
        $europeanCountries = ["fr", "be"];
        $billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $vatApplicable = new VatApplicable($billingInfoRepository->reveal(), $europeanCountries);
        $mode = "DummyMode";
        $vatNumber = "VAT 666";
        $this->assertEquals($vatApplicable->isApplicable($mode, "toto", "mc", $vatNumber), false);
    }


    public function getVATNumber(): array
    {
        return [["", true], ["VAT xxxxx", false]];
    }

    /**
     * @dataProvider getVATNumber()
     */

    public function testShouldApplyVATIfBillingCountryIsInEU(string $vat, bool $assert): void
    {
        $europeanCountries = ["fr", "be"];
        $billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $vatApplicable = new VatApplicable($billingInfoRepository->reveal(), $europeanCountries);

        $mode = "DummyMode";
        $billingCountry = "fr";

        $this->assertEquals($vatApplicable->isApplicable($mode, "toto", $billingCountry, $vat), $assert);
    }


    public function testShouldReturnTrueIfVATISApplicatbleOnSheet(): void
    {

        $event = $this->prophesize(Event::class);
        $event->getMode()->shouldBeCalled()->willReturn(Event::VAT_MODE_ATI);
        $event->getCountry()->shouldBeCalled()->willReturn("tourte");

        $sheet =  $this->prophesize(Sheet::class);
        $sheet->getEvent()->shouldBeCalled()->willReturn($event->reveal());

        $address = $this->prophesize(Address::class);
        $address->getCountry()->shouldBeCalled()->willReturn("tourte");

        $billingInfo = $this->prophesize(BillingInfo::class);
        $billingInfo->getAddress()->shouldBeCalled()->willReturn($address->reveal());
        $billingInfo->getVATNumber()->shouldBeCalled()->willReturn("XXXXXX");

        $billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $billingInfoRepository->getBySheet($sheet->reveal())->shouldBeCalled()->willReturn($billingInfo->reveal());


        $europeanCountries = ["fr", "be"];
        $vatApplicable = new VatApplicable($billingInfoRepository->reveal(), $europeanCountries);

        $this->assertEquals($vatApplicable->onSheet($sheet->reveal()), false);
    }

    public function testShouldThrowIfNoSheetFound(): void
    {

        $sheet =  $this->prophesize(Sheet::class);
        $billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $billingInfoRepository->getBySheet($sheet->reveal())->shouldBeCalled()->willReturn(null);
        $europeanCountries = ["fr", "be"];
        $vatApplicable = new VatApplicable($billingInfoRepository->reveal(), $europeanCountries);

        $this->expectException(MissingBillingInfoException::class);
        $vatApplicable->onSheet($sheet->reveal());
    }
}
