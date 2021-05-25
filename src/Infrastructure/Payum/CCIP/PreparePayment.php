<?php

namespace Proximum\Vimeet\Infrastructure\Payum\CCIP;

use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Storage\StorageInterface;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Domain\Model\Payment\Payment;
use Proximum\Vimeet\Domain\Model\Transaction;
use Proximum\Vimeet\Domain\Repository\BillingInfoRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;

class PreparePayment
{
    const GATEWAY_NAME = 'ccip';

    private Payum $payum;
    private StorageInterface $storage;
    private BillingInfoRepositoryInterface $billingInfoRepository;
    private SheetInfoGuesser $sheetInfoGuesser;
    private TranslatorAdapter $translator;

    public function __construct(
        Payum $payum,
        BillingInfoRepositoryInterface $billingInfoRepository,
        SheetInfoGuesser $sheetInfoGuesser,
        TranslatorAdapter $translator
    ) {
        $this->payum = $payum;
        $this->billingInfoRepository = $billingInfoRepository;
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->translator = $translator;

        $this->storage = $this->payum->getStorage(Payment::class);
    }

    /**
     * @throws \Exception
     *
     * @return TokenInterface
     */
    public function process(Transaction $transaction, string $locale)
    {
        $sheet = $transaction->getSheet();
        $billingInfo = $this->billingInfoRepository->getBySheet($sheet);

        if (null === $billingInfo) {
            throw new \Exception('Billing info is required');
        }

        $event = $sheet->getEvent();

        $description = $this->translator->trans('order.transaction.paypal.description', [
            '%sheetName%' => $this->sheetInfoGuesser->guessSheetTitle($sheet, $locale),
            '%sheetId%' => $sheet->getId(),
            '%eventId%' => $event->getId(),
            '%eventName%' => $event->getTitle(),
        ]);

        $amount = $transaction->getAmountInCents();
        $number = sprintf('%s-%s-%s-%s', $event->getId(), $sheet->getId(), $transaction->getId(), bin2hex(random_bytes(8)));

        $payment = $this->findExistingPayment($transaction);

        if (null === $payment) {
            $payment = $this->storage->create();
            $payment->setTransaction($transaction);
            $payment->setTotalAmount($amount);
            $payment->setNumber($number);
            $payment->setCurrencyCode($transaction->getCurrency());
            $payment->setClientId($sheet->getId());
            $payment->setClientEmail($billingInfo->getEmail());
            $payment->setDescription($description);

            $this->storage->update($payment);
        }

        return $this->payum->getTokenFactory()->createCaptureToken(
            self::GATEWAY_NAME,
            $payment,
            'event_package_payment_done', // the route to redirect after capture
            ['sheet' => $sheet->getId()]
        );
    }

    private function findExistingPayment(Transaction $transaction): ?Payment
    {
        $result = $this->storage->findBy(['transaction' => $transaction]);

        if (count($result) === 0) {
            return null;
        }

        return $result[0];
    }
}
