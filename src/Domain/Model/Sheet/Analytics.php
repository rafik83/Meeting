<?php

namespace Proximum\Vimeet\Domain\Model\Sheet;

use Proximum\Vimeet\Domain\Model\User;

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

    public function incrementViews(User $user): void
    {
        $userId = $user->getId();
        $this->views++;
        if (!in_array($userId, $this->viewers, true)) {
            $this->uniqueViews++;
            array_unshift($this->viewers, $userId);
        }
    }

    public function incrementClicks(User $user, string $objectId, ?int $index): void
    {
        $userId = $user->getId();

        $indices = [$objectId];

        // if index is defined, this is a collection of links
        if (null !== $index) {
            $indices[] = $index;
        }

        // set initial values if empty
        $currentAnalytics = &$this->clickedElements;
        for ($i = 0; $i < count($indices); $i++) {
            if (!isset($currentAnalytics[$indices[$i]])) {
                $currentAnalytics[$indices[$i]] = isset($indices[$i+1]) ? [$indices[$i+1] =>null] : ['views' => 0, 'uniqueViews' => 0, 'viewers' => []];
            }
            $currentAnalytics = &$currentAnalytics[$indices[$i]];
        }

        $currentAnalytics['views']++;
        if (!in_array($userId, $currentAnalytics['viewers'], true)) {
            $currentAnalytics['uniqueViews']++;
            array_unshift($currentAnalytics['viewers'], $userId);
        }
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function getUniqueViews(): int
    {
        return $this->uniqueViews;
    }
}
