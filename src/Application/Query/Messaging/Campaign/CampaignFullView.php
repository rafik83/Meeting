<?php

namespace Proximum\Vimeet\Application\Query\Messaging\Campaign;

class CampaignFullView
{
    /** @var int */
    public $id;

    /** @var string */
    public $title;

    /** @var \DateTimeInterface */
    public $createdAt;

    /** @var string[] */
    public $filters;

    /** @var SheetListView[] */
    public $sheets;

    /** @var string[] */
    public $recipients;

    /**
     * @param int                $id
     * @param string             $title
     * @param \DateTimeInterface $createdAt
     * @param string[]           $filters
     * @param SheetListView[]    $sheets
     * @param string[]           $recipients
     */
    public function __construct(
        $id,
        $title,
        \DateTimeInterface $createdAt,
        array $filters = [],
        array $sheets = [],
        array $recipients = []
    ) {
        $this->id         = $id;
        $this->title      = $title;
        $this->createdAt  = $createdAt;
        $this->filters    = $filters;
        $this->sheets     = $sheets;
        $this->recipients = $recipients;
    }

    /**
     * @param SheetListView $sheet
     */
    public function addSheet(SheetListView $sheet)
    {
        $this->sheets[$sheet->id] =  $sheet;
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addFilter($name, $value)
    {
        $this->filters[$name] = $value;
    }

    /**
     * @param string $recipient
     */
    public function addRecipient($recipient)
    {
        $this->recipients[$recipient] = $recipient;
    }

    /**
     * @return SheetListView[]
     */
    public function getSheets()
    {
        usort($this->sheets, function (SheetListView $a, SheetListView $b) {
            return $a->name > $b->name;
        });

        return $this->sheets;
    }
}
