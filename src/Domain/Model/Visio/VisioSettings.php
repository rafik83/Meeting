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
                    null,
                    null,
                    null,
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

    public function hasHeader(string $locale): bool
    {
        /** @var VisioSettingsTranslation $translation */
        $translation = $this->translations->get($locale);

        if (null === $translation) {
            return false;
        }

        return null !== $translation->getHeader();
    }

    public function hasEndSound(string $locale): bool
    {
        /** @var VisioSettingsTranslation $translation */
        $translation = $this->translations->get($locale);

        if (null === $translation) {
            return false;
        }

        return null !== $translation->getEndSound();
    }

    public function getEndSound(string $locale): ?string
    {
        /** @var VisioSettingsTranslation $translation */
        $translation = $this->translations->get($locale);

        if (null === $translation) {
            return null;
        }

        return $translation->getEndSound();
    }

    public function hasEndImage(string $locale): bool
    {
        /** @var VisioSettingsTranslation $translation */
        $translation = $this->translations->get($locale);

        if (null === $translation) {
            return false;
        }

        return null !== $translation->getEndImage();
    }

    public function getEndImage(string $locale): ?string
    {
        /** @var VisioSettingsTranslation $translation */
        $translation = $this->translations->get($locale);

        if (null === $translation) {
            return null;
        }

        return $translation->getEndImage();
    }

    public function getEndMessage($locale): ?string
    {
        /** @var VisioSettingsTranslation $translation */
        $translation = $this->translations->get($locale);

        if (null === $translation) {
            return null;
        }

        return $translation->getEndMessage();
    }

    public function updateTranslation(
        string $locale,
        ?string $header,
        ?string $endSound,
        ?string $endImage,
        ?string $endMessage
    ): void {
        /** @var VisioSettingsTranslation $translation */
        $translation = $this->translations->get($locale);

        if (null === $translation) {
            $this->translations->set(
                $locale,
                new VisioSettingsTranslation(
                    $this,
                    $locale,
                    $header,
                    $endSound,
                    $endImage,
                    $endMessage
                )
            );

            return;
        }

        $translation->update(
            $header,
            $endSound,
            $endImage,
            $endMessage
        );
    }
}
