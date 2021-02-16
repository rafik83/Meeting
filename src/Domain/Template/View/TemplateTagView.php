<?php

namespace Proximum\Vimeet\Domain\Template\View;

class TemplateTagView
{
    /** @var array */
    public $allTags;

    /** @var string */
    public $participantDataTag;

    /** @var array */
    public $participantTags;

    /** @var string */
    public $sheetDataTag;

    /** @var array */
    public $sheetTags;

    public function __construct(
        array $allTags,
        string $participantDataTag,
        array $participantTags,
        string $sheetDataTag,
        array $sheetTags
    ) {
        $this->allTags = $allTags;
        $this->participantDataTag = $participantDataTag;
        $this->participantTags = $participantTags;
        $this->sheetDataTag= $sheetDataTag;
        $this->sheetTags = $sheetTags;
    }
}
