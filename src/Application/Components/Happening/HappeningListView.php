<?php

namespace Proximum\Vimeet\Application\Components\Happening;

class HappeningListView
{
    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $categoryTitle;

    /**
     * @var \DateTimeInterface
     */
    public $begin;

    /**
     * @var \DateTimeInterface
     */
    public $end;

    /**
     * @var string
     */
    public $title;

    /**
     * @var array
     */
    public $speakers;

    /**
     * @var bool
     */
    public $canUpdate;

    /**
     * @var bool
     */
    public $question;

    /**
     * HappeningListView constructor.
     *
     * @param int                $id
     * @param string             $categoryTitle
     * @param \DateTimeInterface $begin
     * @param \DateTimeInterface $end
     * @param string             $title
     * @param bool               $question
     * @param array              $speakers
     * @param bool               $canUpdate
     */
    public function __construct(
        $id,
        $categoryTitle,
        \DateTimeInterface $begin,
        \DateTimeInterface $end,
        $title,
        $question,
        array $speakers,
        $canUpdate
    ) {
        $this->id            = $id;
        $this->categoryTitle = $categoryTitle;
        $this->begin         = $begin;
        $this->end           = $end;
        $this->title         = $title;
        $this->question      = $question;
        $this->speakers      = $speakers;
        $this->canUpdate     = $canUpdate;
    }
}
