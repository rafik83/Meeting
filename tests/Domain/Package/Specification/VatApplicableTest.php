<?php

namespace Proximum\Vimeet\Tests\Domain\Package\Specification;

use PHPUnit\Framework\TestCase;
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
     * @param string $country
     */

    public function testShouldReturnTrueIfVATIsApplicable($eventCountry, $billingCountry): void
    {

        $europeanCountries = ["fr", "be"];
        $billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $vatApplicable = new VatApplicable($billingInfoRepository->reveal(), $europeanCountries);

        $mode = "DummyMode";
        $vatNumber = 666;

        $this->assertEquals($vatApplicable->isApplicable($mode, $eventCountry, $billingCountry, $vatNumber), true);
    }


    /**
     * @dataProvider getEventCountryAndBillingCountry()
     * @param string $country
     */

    public function testShoulNotdReturnTrueIfVATIsApplicable(): void
    {
        $europeanCountries = ["fr", "be"];
        $billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $vatApplicable = new VatApplicable($billingInfoRepository->reveal(), $europeanCountries);

        $mode = "DummyMode";
        $vatNumber = 666;

        $this->assertEquals($vatApplicable->isApplicable($mode, "toto", "mc", $vatNumber), false);
    }


    public function getVATNumber(): array
    {
        return [[null, true], [333, false]];
    }

    /**
     * @dataProvider getVATNumber()
     * @param string $vat $assert
     */

    public function testShouldApplyVATIFBillingCountryIsInEU($vat, $assert): void
    {
        $europeanCountries = ["fr", "be"];
        $billingInfoRepository = $this->prophesize(BillingInfoRepositoryInterface::class);
        $vatApplicable = new VatApplicable($billingInfoRepository->reveal(), $europeanCountries);

        $mode = "DummyMode";
        $billingCountry = "fr";

        $this->assertEquals($vatApplicable->isApplicable($mode, "toto", $billingCountry, $vat), $assert);
    }
}
