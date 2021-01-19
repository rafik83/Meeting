<?php

namespace Proximum\Vimeet\Application\Command\Sheet\Template;

use DateTimeInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Template\SheetTemplate;

class Duplicate
{
    /**
     * @var string
     */
    public $title;

    /**
     * @var SheetTemplate
     */
    public $template;

    /**
     * @var Event
     */
    public $event;

    /**
     * @var DateTimeInterface
     */
    public $createdAt;

    /**
     * Duplicate constructor.
     *
     * @param SheetTemplate     $template
     * @param DateTimeInterface $createdAt
     */
    public function __construct(SheetTemplate $template, DateTimeInterface $createdAt)
    {
        $this->createdAt = $createdAt;
        $this->template  = $template;
        $this->event     = $template->getEvent(); // set default event
    }
}
