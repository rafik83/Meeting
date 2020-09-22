<?php

namespace Proximum\Vimeet\Domain\View\Template;

class TaggedDataView
{
    /** @var string */
    public $type;

    /** @var bool */
    public $translatable;

    /** @var array */
    public $translations;

    /** @var string */
    public $content;

    /** @var string */
    public $tag;

    /** @var bool */
    public $isTextarea;

    /** @var string|null */
    public $originalUrl;

    public function __construct(
        string $type,
        bool $translatable,
        array $translations,
        string $content,
        string $tag,
        bool $isTextarea,
        ?string $originalUrl
    ) {
        $this->type = $type;
        $this->translatable = $translatable;
        $this->translations = $translations;
        $this->content = $content;
        $this->tag = $tag;
        $this->isTextarea = $isTextarea;
        $this->originalUrl = $originalUrl;
    }
}
