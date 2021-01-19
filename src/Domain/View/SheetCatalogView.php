<?php

namespace Proximum\Vimeet\Domain\View;

class SheetCatalogView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var array
     */
    public $template = [];

    /**
     * @var array
     */
    public $participantTemplate = [];

    /**
     * @var array
     */
    public $participants = [];

    /**
     * SheetCatalogView constructor.
     *
     * @param int   $id
     * @param array $data
     * @param array $template
     * @param array $participantTemplate
     * @param array $participants
     */
    public function __construct($id, array $data, array $template, array $participantTemplate, array $participants)
    {
        $this->id                  = $id;
        $this->data                = $data;
        $this->template            = $template;
        $this->participantTemplate = $participantTemplate;
        $this->participants        = $participants;
    }
}
