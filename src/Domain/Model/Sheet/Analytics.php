<?php

namespace Proximum\Vimeet\Domain\Model\Sheet;

class Analytics
{
    /** @var int */
    private $views = 0;

    /** @var int */
    private $uniqueViews = 0;

    /** @var array user ids of users that have viewed this sheet at least once */
    private $viewers;

    /** @var array */
    private $clickedElements;

    public function __construct()
    {
        $this->viewers = [];
        $this->clickedElements = [];
    }

    public function incrementViews(int $userId)
    {
        $this->views++;
        if (!in_array($userId, $this->viewers, true)) {
            $this->viewers++;
            array_unshift($this->uniqueViewers, $userId);
        }
    }

    /**
     * Get the value of views
     */
    public function getViews(): int
    {
        return $this->views;
    }

    /**
     * Get the value of uniqueViews
     */
    public function getUniqueViews(): int
    {
        return $this->uniqueViews;
    }

}
