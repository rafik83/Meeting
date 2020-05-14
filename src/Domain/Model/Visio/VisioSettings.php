<?php


namespace Proximum\Vimeet\Domain\Model\Visio;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Proximum\Vimeet\Domain\Model\Event;

class VisioSettings
{
    /** @var null|int */
    private $id;

    /** @var Event */
    private $event;

    /** @var Collection|VisioSettingsTranslation[] */
    private $translations;

    public function __construct(Event $event)
    {
        $this->event = $event;
        $this->translations = new ArrayCollection();

        foreach ($event->getLocales() as $locale) {
            $this->translations->set(
                $locale,
                new VisioSettingsTranslation(
                    $this,
                    $locale,
                    null
                )
            );
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    public function getHeader(string $locale): ?string
    {
        /** @var VisioSettingsTranslation $translation */
        $translation = $this->translations->get($locale);

        if (null === $translation) {
            return null;
        }

        return $translation->getHeader();
    }

    public function updateTranslation(
        string $locale,
        ?string $header
    ): void {
        /** @var VisioSettingsTranslation $translation */
        $translation = $this->translations->get($locale);

        if (null === $translation) {
            $this->translations->set(
                $locale,
                new VisioSettingsTranslation($this, $locale, $header)
            );

            return;
        }

        $translation->update($header);
    }
}
