<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Application\View\MeetingRequest\Export;

class SheetView
{
    /** @var int */
    private $id;

    /** @var string|null */
    private $title;

    /** @var string */
    private $typeTitle;

    /** @var string|null */
    public $categoryTitle;

    /** @var int[] */
    private $participantIds;

    /** @var string[] */
    private $participantNames;

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
