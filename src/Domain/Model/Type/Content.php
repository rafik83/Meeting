<?php

namespace Proximum\Vimeet\Domain\Model\Type;

use Doctrine\Common\Collections\ArrayCollection;
use Proximum\Vimeet\Domain\Model\Type;

class Content
{
    public const TYPE_TERMS_OF_SALE = 'terms-of-sale';

    /** @var int|null */
    private $id;

    /** @var string */
    private $type;

    /** @var Type */
    private $associatedParticipationType;

    /** @var ArrayCollection of ContentTranslation */
    private $translations;

    public function __construct(Type $associatedParticipationType, string $type)
    {
        $this->type = $type;
        $this->translations = new ArrayCollection();
        $this->associatedParticipationType = $associatedParticipationType;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAssociatedParticipationType(): Type
    {
        return $this->associatedParticipationType;
    }

    /**
     * @return ContentTranslation[]
     */
    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }

    public function hasTranslation(string $locale): bool
    {
        return $this->translations->containsKey($locale);
    }

    private function getTranslation(string $locale): ?ContentTranslation
    {
        return $this->translations->get($locale);
    }

    public function getValue(string $locale): string
    {
        return $this->hasTranslation($locale)
            ? $this->getTranslation($locale)->getValue() : ''
        ;
    }

    public function translate(string $locale, string $value): void
    {
        if ($this->hasTranslation($locale)) {
            $this->getTranslation($locale)->update($value);
        } else {
            $this->translations->set($locale, new ContentTranslation($this, $locale, $value));
        }
    }
}
