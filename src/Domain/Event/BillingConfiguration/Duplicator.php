<?php

namespace Proximum\Vimeet\Domain\Event\BillingConfiguration;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class Duplicator
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var FileStorageInterface
     */
    private $fileStorage;

    /**
     * Duplicator constructor.
     *
     * @param EventRepositoryInterface $eventRepository
     * @param FileStorageInterface     $fileStorage
     */
    public function __construct(
        EventRepositoryInterface $eventRepository,
        FileStorageInterface $fileStorage
    ) {
        $this->eventRepository = $eventRepository;
        $this->fileStorage     = $fileStorage;
    }

    /**
     * @param Event $event
     */
    public function duplicate(Event $event)
    {
        $eventDuplicated = $event->getDuplicatedFrom();
        $legalInfo       = $eventDuplicated->getConfiguration()->getLegalInfo();

        $event->getConfiguration()->setLegalInfo($legalInfo);

        foreach ($event->getLocales() as $locale) {
            /** @var EventTranslation $eventTranslation */
            $eventTranslation = $event->getTranslations()->get($locale);
            $eventTranslation->setBillingConfiguration(
                $eventDuplicated->getBankInfo($locale),
                $eventDuplicated->getBillingAddress($locale),
                $eventDuplicated->getPaymentCondition($locale),
                $eventDuplicated->getPaymentFooter($locale)
            );
        }

        if (!empty($eventDuplicated->getInvoiceLogo())) {
            $invoiceLogo = $this->fileStorage->copyAndRename($eventDuplicated->getInvoiceLogo());
            $event->setInvoiceLogo($invoiceLogo, $eventDuplicated->getInvoiceLogoExtension());
        }

        $this->eventRepository->set($event);
    }
}
