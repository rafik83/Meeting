<?php

namespace Proximum\Vimeet\Application\Components\Invoice\Denormalizer;

use IntlDateFormatter;
use Proximum\Vimeet\Application\Command\Planning\SheetInfoGuesserCache;
use Proximum\Vimeet\Application\Components\Sheet\SheetInfoGuesser;
use Proximum\Vimeet\Application\View\Invoice\BillingInfosView;
use Proximum\Vimeet\Application\View\Invoice\Vat\VatListView;
use Proximum\Vimeet\Domain\Model\Invoice\Invoice;
use Proximum\Vimeet\Domain\Order\Balance;
use Proximum\Vimeet\Domain\View\Invoice\ExportView;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ExportViewDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    /**
     * @var SheetInfoGuesser
     */
    private $sheetInfoGuesser;

    /**
     * @var Balance
     */
    private $balance;

    /**
     * @param SheetInfoGuesserCache $sheetInfoGuesser
     * @param Balance               $balance
     */
    public function __construct(
        SheetInfoGuesserCache $sheetInfoGuesser,
        Balance $balance
    ) {
        $this->sheetInfoGuesser = $sheetInfoGuesser;
        $this->balance          = $balance;
    }

    /**
     * {@inheritdoc}
     *
     *  @param array $context should contain billingInfosViewOfSheet to be passed to the billingInfosViewDenormalizer
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var Invoice $invoice */
        $invoice = $context['invoice'];

        /** @var BillingInfosView $billingInfo */
        $billingInfo = $this
            ->denormalizer
            ->denormalize($data['billingInfosView'], BillingInfosView::class, $format, $context);

        $sheetTitle = $this->sheetInfoGuesser->guessSheetTitle(
            $invoice->getSheet(),
            $invoice->getEvent()->getAvailableLocale($context['locale'])
        );

        $invoiceDate = $this->getFormattedDate($context['dateFormatter'], $invoice->getCreatedAt());

        return new ExportView(
            $invoice->getEvent()->getId(),
            $invoice->getEvent()->getTitle(),
            $invoice->getSheet()->getOwner()->getId(),
            $invoice->getSheet()->getId(),
            $sheetTitle,
            $invoice->getNumber(),
            $invoice->getVatRate(),
            $invoiceDate,
            $invoice->getTotal(),
            $invoice->getTotalWithVat(),
            $invoice->getVatAmount(),
            $this->balance->getBalance($invoice->getSheet()),
            $invoice->getEvent()->getConfiguration()->getAnalyticsCode(),
            $billingInfo->vatNumber,
            $billingInfo->country,
            $this->getVatListView($data, $format, $context)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return ExportView::class === $type && isset($data['billingInfosView']);
    }

    private function getVatListView($data, $format, array $context): ?VatListView
    {
        if (isset($data['vatListView'])) {
            return $this
                ->denormalizer
                ->denormalize($data['vatListView'], VatListView::class, $format, $context);
        }

        return null;
    }

    /**
     * @param IntlDateFormatter  $dateFormatter
     * @param \DateTimeInterface $date
     *
     * @return bool|string
     */
    private function getFormattedDate(IntlDateFormatter $dateFormatter, \DateTimeInterface $date)
    {
        $dateFormatted = $dateFormatter->format($date);

        return false !== $dateFormatted ? $dateFormatted : '';
    }
}
