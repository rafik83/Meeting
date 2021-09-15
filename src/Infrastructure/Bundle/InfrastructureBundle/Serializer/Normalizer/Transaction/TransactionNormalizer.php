<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Transaction;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Transaction\TransactionListViewQuery;
use Proximum\Vimeet\Application\Serializer\Charset;
use Proximum\Vimeet\Application\Serializer\Normalizer\AbstractNormalizer;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TransactionNormalizer extends AbstractNormalizer implements NormalizerInterface
{
    const TRANSLATION_PREFIX = 'admin.export.transaction.column.';
    const TRANSLATION_DOMAIN = 'messages';

    /** @var array */
    private $timeFormatter = [];

    /** @var string */
    private $toCharset = Charset::WINDOWS_1252;

    /**
     * TransactionNormalizer constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        parent::__construct($translator);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $this->toCharset = $context['charset'] ?? $this->toCharset;

        $data = [];

        foreach ($object->transactionsView as $view) {
            $createdAt = $this->formatDate(
                $view->transactionDate,
                $view->event,
                $object->adminLocale
            );

            $data[] =  [
                $this->convert($this->translate('sheet.id', $object->adminLocale)) => $this->convert($view->sheetId),
                $this->convert($this->translate('event.id', $object->adminLocale)) => $this->convert($view->eventId),
                $this->convert($this->translate('event.name', $object->adminLocale)) => $this->convert($view->eventName),
                $this->convert($this->translate('sheet.owner.id', $object->adminLocale)) => $this->convert($view->sheetOwnerId),
                $this->convert($this->translate('society.name', $object->adminLocale)) => $this->convert($view->companyName),
                $this->convert($this->translate('transaction_date', $object->adminLocale)) => !$createdAt ? null : $this->convert($createdAt),
                $this->convert($this->translate('transaction_type', $object->adminLocale)) => $this->convert($view->transactionType),
                $this->convert($this->translate('transaction_reference', $object->adminLocale)) => $this->convert($view->transactionReference),
                $this->convert($this->translate('payment.gateway', $object->adminLocale)) => $this->convert($view->transactionGateway),
                $this->convert($this->translate('transaction_amount', $object->adminLocale)) => $this->convert($view->transactionAmount),
                $this->convert($this->translate('billing_contact.country', $object->adminLocale)) => $this->convert($view->contactBillingInfoCountry),
                $this->convert($this->translate('billing_contact.vat_number', $object->adminLocale)) => $this->convert($view->vatNumber),
            ];
        }

        return $data;
    }

    public function convert($value)
    {
        return $this->convertCharset(
            $value,
            Charset::UTF_8,
            $this->toCharset
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof TransactionListViewQuery;
    }

    /**
     * @param string $key
     * @param string $locale
     *
     * @return string
     */
    private function translate($key, $locale)
    {
        return $this->translator->trans(self::TRANSLATION_PREFIX . $key, [], self::TRANSLATION_DOMAIN, $locale);
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @param Event              $event
     * @param string             $locale
     *
     * @return bool|string
     */
    private function formatDate(\DateTimeInterface $dateTime, Event $event, $locale)
    {
        if (!isset($this->timeFormatter[$event->getId()])) {
            $this->timeFormatter[$event->getId()] = \IntlDateFormatter::create(
                $locale,
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::NONE,
                $event->getTimeZone()
            );
        }

        return $this->timeFormatter[$event->getId()]->format($dateTime);
    }
}
