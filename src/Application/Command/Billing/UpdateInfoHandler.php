<?php

namespace Proximum\Vimeet\Application\Command\Billing;

use Proximum\Vimeet\Domain\Model\Address;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class UpdateInfoHandler
{
    /**
     * @var BillingInfoRepositoryInterface
     */
    private $billingInfoRepository;

    /**
     * UpdateInfoHandler constructor.
     *
     * @param BillingInfoRepositoryInterface $billingInfoRepository
     */
    public function __construct(BillingInfoRepositoryInterface $billingInfoRepository)
    {
        $this->billingInfoRepository = $billingInfoRepository;
    }

    /**
     * @param UpdateInfo $updateInfo
     */
    public function handle(UpdateInfo $updateInfo)
    {
        $updateInfo->billingInfo->update(
            $updateInfo->gender,
            $updateInfo->lastname,
            $updateInfo->firstname,
            $updateInfo->function,
            $updateInfo->phone,
            $updateInfo->mobile,
            $updateInfo->email,
            $updateInfo->company,
            new Address($updateInfo->street, $updateInfo->zipcode, $updateInfo->city, $updateInfo->country),
            $updateInfo->vatNumber,
            $updateInfo->reference
        );

        if ($updateInfo->billingInfo->getId()) {
            $this->billingInfoRepository->set($updateInfo->billingInfo);
        } else {
            $this->billingInfoRepository->add($updateInfo->billingInfo);
        }
    }
}
