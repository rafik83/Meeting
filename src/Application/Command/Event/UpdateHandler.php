<?php

namespace Proximum\Vimeet\Application\Command\Event;

use Proximum\Vimeet\Application\Event\Event\LocaleChangedEvent;
use Proximum\Vimeet\Application\Event\Events;
use Proximum\Vimeet\Application\Exception\Event\DomainAlreadyUsedException;
use Proximum\Vimeet\Domain\Model\EventTranslation;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UpdateHandler
{
    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->eventRepository = $eventRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Update $update
     *
     * @throws DomainAlreadyUsedException
     */
    public function handle(Update $update): void
    {
        $isLocalesUpdated = $update->isLocalesUpdated();

        if ($update->domain !== $update->event->getDomain()) {
            $foundEvent = $this->eventRepository->getEventByDomain($update->domain);

            if (null !== $foundEvent && $update->event !== $foundEvent) {
                throw new DomainAlreadyUsedException(sprintf('Given domain %s', $update->domain));
            }
        }

        $event = $update->event;
        $apiKey = $event->getApiKey();
        if (null === $apiKey && $update->apiKeyAvailable) {
            $apiKey = hash_hmac('SHA1', random_bytes(8), random_bytes(4));
        }
        if (null !== $apiKey && !$update->apiKeyAvailable) {
            $apiKey = null;
        }

        $event->update(
            $update->title,
            $update->locales,
            $update->fallback,
            $update->mode,
            $update->vat,
            $update->country,
            $update->currency,
            $update->timeZone,
            $update->domain,
            $update->organiserName,
            $update->emailTeam,
            $update->invoicePrefix,
            $update->visible,
            $update->welcomeEnabled,
            $update->disabledEmailChanging,
            $update->disabledPasswordChanging,
            $update->googleLoginEnabled,
            $update->linkedinLoginEnabled,
            $update->accessControlEnabled,
            $update->showCheckinStatus,
            $update->autoArchiveWebinar,
            $apiKey
        );

        $event->getConfiguration()->setVisio($update->visio);

        $event->getConfiguration()->setParticipantInfoToDisplayOnPlanning(
            $update->displayParticipantNameOnPlanning,
            $update->displayParticipantPositionOnPlanning
        );

        $update->event->getConfiguration()->setAnalyticsCode($update->analyticsCode);

        $this->updateTranslations($update);

        $this->eventRepository->set($event);

        if ($isLocalesUpdated) {
            $localeChangedEvent = new LocaleChangedEvent($event);
            $this->eventDispatcher->dispatch(Events::EVENT_LOCALE_CHANGED, $localeChangedEvent);
        }
    }

    /**
     * @param Update $update
     */
    private function updateTranslations(Update $update): void
    {
        // Create missing translation
        foreach ($update->event->getLocales() as $locale) {
            if (!$update->event->getTranslations()->get($locale)) {
                $update->event->getTranslations()->set($locale, new EventTranslation($update->event, $locale, ''));
            }
        }

        // Update translations
        foreach ($update->event->getTranslations() as $translation) {
            if (isset($update->translations[$translation->getLocale()])) {
                $translation->update($update->translations[$translation->getLocale()]['description']);
            }

            // Remove deleted translations
            if (!$update->event->hasLocale($translation->getLocale())) {
                $update->event->getTranslations()->removeElement($translation);
            }
        }
    }
}
