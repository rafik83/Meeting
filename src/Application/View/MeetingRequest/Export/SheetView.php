<?php

namespace Proximum\Vimeet\Application\View\MeetingRequest\Export;

class SheetView
{
    /** @var int */
    public $id;

    /** @var string|null */
    public $title;

    /** @var string */
    public $typeTitle;

    /** @var string|null */
    public $categoryTitle;

    /** @var int[] */
    public $participantIds;

    /** @var string[] */
    public $participantNames;

    /**
     * @param int         $id
     * @param string|null $title
     * @param string      $typeTitle
     * @param string|null $categoryTitle
     * @param int[]       $participantIds
     * @param string[]    $participantNames
     */
    public function __construct(
        int $id,
        string $title = null,
        string $typeTitle,
        string $categoryTitle = null,
        array $participantIds,
        array $participantNames
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->typeTitle = $typeTitle;
        $this->categoryTitle = $categoryTitle;
        $this->participantIds = $participantIds;
        $this->participantNames = $participantNames;
    }
}
