<?php

namespace Proximum\Vimeet\Application\Query\Contact;

class ContactSheetView
{
    /** @var string */
    public $title;

    /** @var string */
    public $url;

    public function __construct(string $title, string $url)
    {
        $this->title = $title;
        $this->url = $url;
    }
}
