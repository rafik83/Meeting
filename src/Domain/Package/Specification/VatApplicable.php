<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Domain\Package\Specification;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class VatApplicable
{
    /**
     * @var array
     */
    private $europeanCountries;

    /**
     * @var BillingInfoRepositoryInterface
     */
    private $billingInfoRepository;

    /**
     * VatApplicable constructor.
     *
     * @param BillingInfoRepositoryInterface $billingInfoRepository
     * @param array                          $europeanCountries
     */
    public function __construct(BillingInfoRepositoryInterface $billingInfoRepository, array $europeanCountries)
    {
        $this->billingInfoRepository = $billingInfoRepository;
        $this->europeanCountries     = $europeanCountries;
    }

    /**
     * @param Sheet $sheet
     *
     * @throws MissingBillingInfoException
     *
     * @return bool
     */
    public function onSheet(Sheet $sheet)
    {
        $billingInfo = $this->billingInfoRepository->getBySheet($sheet);

        if (null === $billingInfo) {
            throw new MissingBillingInfoException('missing billing info');
        }

        return $this->isApplicable(
            $sheet->getEvent()->getMode(),
            $sheet->getEvent()->getCountry(),
            $billingInfo->getAddress()->getCountry(),
            $billingInfo->getVatNumber()
        );
    }

    /**
     * @param string $mode
     * @param string $eventCountry
     * @param string $billingCountry
     * @param string $vatNumber
     *
     * @return bool
     */
    private function isApplicable($mode, $eventCountry, $billingCountry, $vatNumber)
    {
        if (Event::VAT_MODE_ATI === $mode) {
            return false;
        }

        // Billing country and event country are the same
        if (strtoupper($billingCountry) === strtoupper($eventCountry)) {
            return true;
        }

        // Billing country is in the EU and there is not billing vat number
        if (in_array(strtolower($billingCountry), array_map('strtolower', $this->europeanCountries)) && !$vatNumber) {
            return true;
        }

        return false;
    }
}
