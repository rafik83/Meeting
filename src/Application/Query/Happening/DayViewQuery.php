<?php

namespace Proximum\Vimeet\Application\Query\Happening;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Happening\Category;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Model\Unavailability\Mass;
use Proximum\Vimeet\Domain\Model\User;
use Proximum\Vimeet\Domain\Time\TimeRangeView;

class DayViewQuery
{
    /** @var TimeRangeView */
    public $timeRange;

    /** @var string */
    public $locale;

    /** @var Event */
    public $event;

    /** @var Category|null */
    public $category;

    /** @var Mass[] */
    public $masses;

    /** @var Sheet */
    public $sheet;

    /** @var User */
    public $user;

    /**
     * @param Event         $event
     * @param Sheet         $sheet
     * @param User          $user
     * @param TimeRangeView $timeRange
     * @param string        $locale
     * @param Category|null $category
     * @param Mass[]        $masses
     */
    public function __construct(
        Event $event,
        Sheet $sheet,
        User $user,
        TimeRangeView $timeRange,
        string $locale,
        Category $category = null,
        array $masses = []
    ) {
        $this->locale = $locale;
        $this->event = $event;
        $this->timeRange = $timeRange;
        $this->category = $category;
        $this->masses = $masses;
        $this->sheet = $sheet;
        $this->user = $user;
    }
}
