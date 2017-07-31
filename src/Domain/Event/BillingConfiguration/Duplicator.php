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
        $eventDuplicatedFrom = $event->getDuplicatedFrom();
        $legalInfo           = $eventDuplicatedFrom->getConfiguration()->getLegalInfo();

        $event->getConfiguration()->setLegalInfo($legalInfo);

        foreach ($event->getLocales() as $locale) {
            /** @var EventTranslation $eventTranslation */
            $eventTranslation = $event->getTranslations()->get($locale);
            $eventTranslation->setBillingConfiguration(
                $eventDuplicatedFrom->getBankInfo($locale),
                $eventDuplicatedFrom->getBillingAddress($locale),
                $eventDuplicatedFrom->getPaymentCondition($locale),
                $eventDuplicatedFrom->getPaymentFooter($locale)
            );
        }

        if (!empty($eventDuplicatedFrom->getInvoiceLogo())) {
            $invoiceLogo = $this->fileStorage->copyAndRename($eventDuplicatedFrom->getInvoiceLogo());
            $event->setInvoiceLogo($invoiceLogo, $eventDuplicatedFrom->getInvoiceLogoExtension());
        }

        $this->eventRepository->add($event);
    }
}
