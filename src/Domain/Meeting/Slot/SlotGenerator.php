<?php

namespace Proximum\Vimeet\Domain\Meeting\Slot;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\MeetingSlot;

class SlotGenerator
{
    /**
     * @param Event    $event
     * @param Recipe[] $recipes
     *
     * @return MeetingSlot[]
     */
    public function generate(Event $event, array $recipes)
    {
        $slots = [];

        foreach ($recipes as $recipe) {
            $interval = new \DateInterval(sprintf('PT%sM', $recipe->duration + $recipe->interval));
            $period   = new \DatePeriod($recipe->begin, $interval, $recipe->end);

            foreach ($period as $date) {
                $end = clone $date;
                $end->modify(sprintf('+%sminutes', $recipe->duration));

                $slots[] = new MeetingSlot($event, $date, $end);
            }
        }

        return $slots;
    }
}
