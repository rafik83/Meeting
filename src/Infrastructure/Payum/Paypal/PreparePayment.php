<?php

namespace Proximum\Vimeet\Infrastructure\Payum\Paypal;

use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;

class PreparePayment
{
    const GATEWAY_NAME = 'paypal_express_checkout';

    /**
     * @var Payum
     */
    private $payum;

    /**
     * @var BillingInfoRepositoryInterface
     */
    private $billingInfoRepository;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var TranslatorAdapter
     */
    private $translator;

    /**
     * @param Payum                          $payum
     * @param BillingInfoRepositoryInterface $billingInfoRepository
     * @param SheetInfoGuesser               $sheetInfoGuesser
     * @param TranslatorAdapter              $translator
     */
    public function __construct(
        Payum $payum,
        BillingInfoRepositoryInterface $billingInfoRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        TranslatorAdapter $translator
    ) {
        $this->payum                 = $payum;
        $this->billingInfoRepository = $billingInfoRepository;
        $this->sheetInfoGuesser      = $sheetInfoGuesser;
        $this->translator            = $translator;
    }

    /**
     * @param Transaction $transaction
     * @param string      $locale
     *
     * @throws \Exception
     *
     * @return TokenInterface
     */
    public function process(Transaction $transaction, $locale)
    {
        $sheet = $transaction->getSheet();
        $billingInfo = $this->billingInfoRepository->getBySheet($sheet);

        if (null === $billingInfo) {
            throw new \Exception('Billing info is required');
        }

        $event = $sheet->getEvent();

        $storage = $this->payum->getStorage(Payment::class);

        $description = $this->translator->trans('order.transaction.paypal.description', [
            '%sheetName%' => $this->sheetInfoGuesser->guessSheetTitle($sheet, $locale),
            '%sheetId%'   => $sheet->getId(),
            '%eventId%'   => $event->getId(),
            '%eventName%' => $event->getTitle(),
        ]);

        $amount = $transaction->getAmountInCents();
        $number = sprintf('%s-%s-%s-%s', $event->getId(), $sheet->getId(), $transaction->getId(), uniqid());

        /** @var Payment $payment */
        $payment = $storage->create();
        $payment->setTransaction($transaction);
        $payment->setTotalAmount($amount);
        $payment->setNumber($this->truncateSingleByte127($number));
        $payment->setCurrencyCode($transaction->getCurrency());
        $payment->setClientId($sheet->getId());
        $payment->setClientEmail($this->truncateSingleByte127($billingInfo->getEmail()));
        $payment->setDescription($this->truncateSingleByte127($description));

        $payment->setDetails(
            [
                'NOSHIPPING'  => '1',
                'FIRSTNAME'   => $this->truncateDoubleByte64($billingInfo->getFirstname()),
                'LASTNAME'    => $this->truncateDoubleByte64($billingInfo->getLastname()),
                'COUNTRYCODE' => $billingInfo->getAddress()->getCountry(),
                'LOCALECODE'  => strtoupper($locale),
            ]
        );

        $storage->update($payment);

        return $this->payum->getTokenFactory()->createCaptureToken(
            self::GATEWAY_NAME,
            $payment,
            'event_package_payment_done', // the route to redirect after capture
            ['sheet' => $sheet->getId()]
        );
    }

    /**
     * Paypal has for some fields a character length and limitations: 127 single-byte alphanumeric characters.
     *
     * @param $value
     *
     * @return string
     */
    private function truncateSingleByte127($value)
    {
        return substr($value, 0, 127);
    }

    /**
     * Paypal has for some fields a character length and limitations: 64 double-byte characters.
     *
     * @param $value
     *
     * @return string
     */
    private function truncateDoubleByte64($value)
    {
        return mb_substr($value, 0, 64);
    }
}
