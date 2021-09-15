<?php

namespace Proximum\Vimeet\Application\Query\Transaction;

use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\View\Transaction\TransactionView;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;

class TransactionViewQueryHandler
{
    const PAYPAL_TRANSACTION_ID_KEY = 'PAYMENTINFO_0_TRANSACTIONID';

    /**
     * @var SheetInfoGuesserCache
     */
    private $sheetInfoGuesser;

    /**
     * @var BillingInfoRepositoryInterface
     */
    private $billingInfoRepository;

    /**
     * @var array
     */
    private $billingInfo = [];

    /**
     * TransactionViewQueryHandler constructor.
     *
     * @param SheetInfoGuesserCache          $sheetInfoGuesser
     * @param BillingInfoRepositoryInterface $billingInfoRepository
     */
    public function __construct(
        SheetInfoGuesserCache $sheetInfoGuesser,
        BillingInfoRepositoryInterface $billingInfoRepository
    ) {
        $this->sheetInfoGuesser      = $sheetInfoGuesser;
        $this->billingInfoRepository = $billingInfoRepository;
    }

    /**
     * @param Sheet[] $sheets
     */
    public function preloadBillingInfo(array $sheets)
    {
        $billingInfos = $this->billingInfoRepository->getBySheets($sheets);

        foreach ($billingInfos as $billingInfo) {
            $this->billingInfo[$billingInfo->getSheet()->getId()] = $billingInfo;
        }
    }

    /**
     * @param TransactionViewQuery $query
     *
     * @return TransactionView
     */
    public function handle(TransactionViewQuery $query)
    {
        $paypalGateWay = null;

        if (null !== $query->payment) {
            $paymentDetails = $query->payment->getDetails();

            if ('paypal' === $query->transaction->getMode() && isset($paymentDetails[self::PAYPAL_TRANSACTION_ID_KEY])) {
                $paypalGateWay = $paymentDetails[self::PAYPAL_TRANSACTION_ID_KEY];
            }
        }

        $sheetTitle = $this->sheetInfoGuesser->guessSheetTitle($query->sheet, $query->locale);

        if (!isset($this->billingInfo[$query->sheet->getId()])) {
            $this->billingInfo[$query->sheet->getId()] = $this->billingInfoRepository->getBySheet($query->sheet);
        }

        $billingInfos   = $this->billingInfo[$query->sheet->getId()];
        $billingCountry = !$billingInfos ? null : $billingInfos->getAddress()->getCountry();
        $billingVat     = !$billingInfos ? null : $billingInfos->getVatNumber();

        return new TransactionView(
            $query->event,
            $query->sheet->getId(),
            $query->event->getId(),
            $query->event->getTitle(),
            $query->sheet->getOwner()->getId(),
            $sheetTitle,
            $query->transaction->getDate(),
            $query->transaction->getMode(),
            $query->transaction->getReference(),
            $paypalGateWay,
            $query->transaction->getAmount(),
            $billingCountry,
            $billingVat
        );
    }
}
