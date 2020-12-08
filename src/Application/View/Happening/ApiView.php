<?php

namespace Proximum\Vimeet\Application\View\Happening;

class ApiView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var string */
    public $begin;

    /** @var string */
    public $end;

    /** @var string */
    public $type;

    /** @var string */
    public $category;

    /** @var ApiSpeakerView[]|null */
    public $speakers;

    /** @var int[] */
    public $participantTypes;

    public function __construct(
        int $id,
        string $title,
        string $description,
        string $begin,
        string $end,
        string $type,
        string $category,
        array $speakers,
        array $participantTypes
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->begin = $begin;
        $this->end = $end;
        $this->type = $type;
        $this->category = $category;
        $this->speakers = $speakers;
        $this->participantTypes = $participantTypes;
    }
}
