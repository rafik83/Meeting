<?php

namespace Proximum\Vimeet\Application\Query\Order;

use Proximum\Vimeet\Application\View\Order\ProFormaView;
use Proximum\Vimeet\Domain\Model\Type\PaymentConditions;
use Proximum\Vimeet\Domain\Package\Exception\MissingBillingInfoException;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class ProFormaQueryHandler
{
    /** @var SummaryQueryHandler */
    private $summaryQueryHandler;

    /** @var BillingInfoRepositoryInterface */
    private $billingInfoRepository;

    /**
     * @param SummaryQueryHandler            $summaryQueryHandler
     * @param BillingInfoRepositoryInterface $billingInfoRepository
     */
    public function __construct(
        SummaryQueryHandler $summaryQueryHandler,
        BillingInfoRepositoryInterface $billingInfoRepository
    ) {
        $this->summaryQueryHandler = $summaryQueryHandler;
        $this->billingInfoRepository = $billingInfoRepository;
    }

    /**
     * @param ProFormaQuery $proFormaQuery
     *
     * @throws MissingBillingInfoException
     *
     * @return ProFormaView
     */
    public function handle(ProFormaQuery $proFormaQuery)
    {
        $locale      = $proFormaQuery->locale;
        $billingInfo = $this->billingInfoRepository->getBySheet($proFormaQuery->sheet);

        if (null === $billingInfo) {
            throw new MissingBillingInfoException('Missing billing info');
        }

        $paymentConditions = $proFormaQuery->sheet->getType()->getPaymentConditions();

        if ($paymentConditions instanceof PaymentConditions) {
            $bankInfo = $paymentConditions->getBankInfo($locale);
            $billingAddress = $paymentConditions->getBillingAddress($locale);
            $paymentCondition = $paymentConditions->getPaymentCondition($locale);
            $paymentFooter = $paymentConditions->getPaymentFooter($locale);
        } else {
            $event = $proFormaQuery->sheet->getEvent();

            $bankInfo = $event->getBankInfo($locale);
            $billingAddress = $event->getBillingAddress($locale);
            $paymentCondition = $event->getPaymentCondition($locale);
            $paymentFooter = $event->getPaymentFooter($locale);
        }

        return new ProFormaView(
            $proFormaQuery->sheet,
            $proFormaQuery->order,
            $billingInfo,
            $this->summaryQueryHandler->handle(new SummaryQuery($proFormaQuery->sheet, $proFormaQuery->order, $locale)),
            $proFormaQuery->sheet->getEvent()->getLegalInformation(),
            $bankInfo,
            $billingAddress,
            $paymentCondition,
            $paymentFooter
        );
    }
}
