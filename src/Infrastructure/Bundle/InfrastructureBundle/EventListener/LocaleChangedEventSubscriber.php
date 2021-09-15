<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\EventListener;

use Proximum\Vimeet\Application\Event\Event\LocaleChangedEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Domain\Repository\Template\RegistrationTemplateRepositoryInterface;
use Proximum\Vimeet\Domain\Repository\Template\SheetTemplateRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocaleChangedEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var SheetTemplateRepositoryInterface
     */
    private $sheetTemplateRepository;
    /**
     * @var RegistrationTemplateRepositoryInterface
     */
    private $registrationTemplateRepository;

    /**
     * @param SheetTemplateRepositoryInterface        $sheetTemplateRepository
     * @param RegistrationTemplateRepositoryInterface $registrationTemplateRepository
     */
    public function __construct(
        SheetTemplateRepositoryInterface $sheetTemplateRepository,
        RegistrationTemplateRepositoryInterface $registrationTemplateRepository
    ) {
        $this->sheetTemplateRepository        = $sheetTemplateRepository;
        $this->registrationTemplateRepository = $registrationTemplateRepository;
    }

    /**
     * @param LocaleChangedEvent $localeChangedEvent
     */
    public function onEventLocaleChanged(LocaleChangedEvent $localeChangedEvent)
    {
        $event = $localeChangedEvent->getEvent();
        $sheetTemplates = $this
            ->sheetTemplateRepository
            ->getTemplateForGivenEvent($event);

        $registrationTemplates = $this
            ->registrationTemplateRepository
            ->getTemplateForGivenEvent($event);

        foreach ($sheetTemplates as $sheetTemplate) {
            if ($sheetTemplate->getLocales() !== $event->getLocales()
                || $sheetTemplate->getFallback() !== $event->getFallback()
            ) {
                $sheetTemplate->updateLocales($event->getLocales(), $event->getFallback());
                $this->sheetTemplateRepository->set($sheetTemplate);
            }
        }

        foreach ($registrationTemplates as $registrationTemplate) {
            if ($registrationTemplate->getLocales() !== $event->getLocales()
                || $registrationTemplate->getFallback() !== $event->getFallback()
            ) {
                $registrationTemplate->updateLocales($event->getLocales(), $event->getFallback());
                $this->registrationTemplateRepository->set($registrationTemplate);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::EVENT_LOCALE_CHANGED => 'onEventLocaleChanged',
        ];
    }
}
