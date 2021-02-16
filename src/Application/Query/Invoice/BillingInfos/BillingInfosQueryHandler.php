<?php

namespace Proximum\Vimeet\Application\Query\Invoice\BillingInfos;

use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class BillingInfosQueryHandler
{
    /** @var BillingInfoRepositoryInterface */
    private $billingInfosRepository;

    /**
     * @param BillingInfoRepositoryInterface $billingInfosRepository
     */
    public function __construct(BillingInfoRepositoryInterface $billingInfosRepository)
    {
        $this->billingInfosRepository = $billingInfosRepository;
    }

    /**
     * @param BillingInfosQuery $billingInfosQuery
     *
     * @throws MissingBillingInfoException
     *
     * @return BillingInfosView
     */
    public function handle(BillingInfosQuery $billingInfosQuery)
    {
        $billingInfo = $this->billingInfosRepository->getBySheet($billingInfosQuery->sheet);

        if (null === $billingInfo) {
            throw new MissingBillingInfoException(
                sprintf('Missing billing info for sheet %s', $billingInfosQuery->sheet->getId())
            );
        }

        return new BillingInfosView(
            $billingInfo->getGender(),
            $billingInfo->getLastname(),
            $billingInfo->getFirstname(),
            $billingInfo->getFunction(),
            $billingInfo->getPhone(),
            $billingInfo->getMobile(),
            $billingInfo->getEmail(),
            $billingInfo->getCompany(),
            $billingInfo->getAddress()->getStreet(),
            $billingInfo->getAddress()->getZipcode(),
            $billingInfo->getAddress()->getCity(),
            $billingInfo->getAddress()->getCountry(),
            $billingInfo->getVatNumber(),
            $billingInfo->getReference()
        );
    }
}
