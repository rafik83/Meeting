<?php

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Application\Adapter\FileStorageInterface;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;

class BillingConfigurationHandler
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
     * @param EventRepositoryInterface $eventRepository
     * @param FileStorageInterface     $fileStorage
     */
    public function __construct(EventRepositoryInterface $eventRepository, FileStorageInterface $fileStorage)
    {
        $this->eventRepository = $eventRepository;
        $this->fileStorage     = $fileStorage;
    }

    /**
     * @param BillingConfiguration $billingConfiguration
     *
     * @return \Proximum\Vimeet\Domain\Model\Event
     */
    public function handle(BillingConfiguration $billingConfiguration)
    {
        $billingConfiguration
            ->event
            ->getConfiguration()
            ->setLegalInfo($billingConfiguration->legalInfo);

        if (null !== $billingConfiguration->invoiceLogo) {
            $invoiceLogoExtension = $this->fileStorage->getExtension($billingConfiguration->invoiceLogo);
            $invoiceLogoPath      = $this->fileStorage->upload($billingConfiguration->invoiceLogo);
            $billingConfiguration->event->setInvoiceLogo($invoiceLogoPath, $invoiceLogoExtension);
        }

        foreach ($billingConfiguration->event->getLocales() as $locale) {
            if (!$billingConfiguration->event->getTranslations()->get($locale)) {
                $eventTranslation = new EventTranslation($billingConfiguration->event, $locale, '');

                $billingConfiguration->event->getTranslations()->set($locale, $eventTranslation);
            }
        }

        foreach ($billingConfiguration->translations as $locale => $translation) {
            /** @var EventTranslation $eventTranslation */
            $eventTranslation = $billingConfiguration->event->getTranslations()->get($locale);

            $eventTranslation->setBillingConfiguration(
                $billingConfiguration->translations[$locale]['bankInfo'],
                $billingConfiguration->translations[$locale]['billingAddress'],
                $billingConfiguration->translations[$locale]['paymentCondition'],
                $billingConfiguration->translations[$locale]['paymentFooter']
            );
        }

        $this->eventRepository->add($billingConfiguration->event);

        return $billingConfiguration->event;
    }
}
