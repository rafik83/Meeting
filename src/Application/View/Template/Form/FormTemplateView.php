<?php

namespace Proximum\Vimeet\Application\View\Template\Form;

use Proximum\Vimeet\Domain\Model\Type;

class FormTemplateView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var bool */
    public $isPublished;

    /** @var string[] indexed by locale */
    public $translatedTitles;

    /** @var Type[] */
    public $types;

    /** @var string */
    public $url;

    /** @var string */
    public $locale;

    /** @var \DateTimeInterface */
    public $createdAt;

    public function __construct(
        int $id,
        string $title,
        bool $isPublished,
        array $translatedTitles,
        array $types,
        string $url,
        string $locale,
        \DateTimeInterface $createdAt
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->isPublished = $isPublished;
        $this->translatedTitles = $translatedTitles;
        $this->types = $types;
        $this->url = $url;
        $this->locale = $locale;
        $this->createdAt = $createdAt;
    }
}
