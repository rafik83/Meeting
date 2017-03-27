<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Serializer\Normalizer\Transaction;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Query\Transaction\TransactionListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TransactionNormalizer implements NormalizerInterface
{
    const TRANSLATION_PREFIX = 'admin.export.transaction.column.';
    const TRANSLATION_DOMAIN = 'messages';
    
    /**
     * @var TranslatorInterface
     */
    private $translator;
    
    /**
     * TransactionNormalizer constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }
    
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $data = [];
        
        foreach ($object->transactionsView as $view) {
            $createdAt = $this->formatDate(
                $view->transactionDate->getTimestamp(),
                $view->event,
                $object->adminLocale
            );
            
            $data[] =  [
                $this->translate('sheet.id', $object->adminLocale) => $view->sheetId,
                $this->translate('event.id', $object->adminLocale) => $view->eventId,
                $this->translate('event.name', $object->adminLocale) => $view->eventName,
                $this->translate('sheet.owner.id', $object->adminLocale) => $view->sheetOwnerId,
                $this->translate('society.name', $object->adminLocale) => $view->companyName,
                $this->translate('transaction_date', $object->adminLocale) => !$createdAt ? null : $createdAt,
                $this->translate('transaction_type', $object->adminLocale) => $view->transactionType,
                $this->translate('transaction_reference', $object->adminLocale) => $view->transactionReference,
                $this->translate('payment.gateway', $object->adminLocale) => $view->transactionGateway,
                $this->translate('transaction_amount', $object->adminLocale) => $view->transactionAmount,
                $this->translate('billing_contact.country', $object->adminLocale) => $view->contactBillingInfoCountry,
                $this->translate('billing_contact.vat_number', $object->adminLocale) => $view->vatNumber
            ];
        }
        
        return $data;
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
        return $this->translator->trans(self::TRANSLATION_PREFIX.$key, [], self::TRANSLATION_DOMAIN, $locale);
    }
    
    /**
     * @param int       $dateTime
     * @param Event     $event
     * @param string    $locale
     *
     * @return bool|string
     */
    private function formatDate($dateTime, Event $event, $locale)
    {
        $timeFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            $event->getTimeZone()
        );
        
        return $timeFormatter->format($dateTime);
    }
}
