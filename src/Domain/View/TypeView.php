<?php

namespace Proximum\Vimeet\Domain\View;

class TypeView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var string */
    public $description;

    /** @var bool */
    public $hidden;

    public function __construct(int $id, string $title, ?string $description, bool $hidden)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->hidden = $hidden;
    }
}
