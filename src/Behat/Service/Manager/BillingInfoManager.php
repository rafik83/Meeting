<?php

namespace Proximum\Vimeet\Behat\Service\Manager;

use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Model\BillingInfo;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class BillingInfoManager
{
    /** @var BillingInfoRepositoryInterface */
    private $billingInfoRepository;

    /**
     * @param BillingInfoRepositoryInterface $billingInfoRepository
     */
    public function __construct(BillingInfoRepositoryInterface $billingInfoRepository)
    {
        $this->billingInfoRepository = $billingInfoRepository;
    }

    /**
     * @param Sheet  $sheet
     * @param string $country
     * @param string $vatNumber
     *
     * @return BillingInfo
     */
    public function create(Sheet $sheet, $country = 'FR', $vatNumber = '')
    {
        $billingInfo = new BillingInfo($sheet);

        $billingInfo->update(
            'man',
            'Sebastien',
            'Patrick',
            'function',
            '+33122334455',
            '+33622334455',
            'MyCompany',
            'email@example.net',
            new Address('street', 'zipcode', 'city', $country),
            $vatNumber,
            ''
        );

        $this->billingInfoRepository->add($billingInfo);

        return $billingInfo;
    }
}
