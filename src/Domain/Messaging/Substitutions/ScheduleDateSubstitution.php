<?php

namespace Proximum\Vimeet\Domain\Messaging\Substitutions;

use Proximum\Vimeet\Domain\Model\Sheet;

class ScheduleDateSubstitution implements SubstituteInterface
{
    /**
     * {@inheritdoc}
     */
    public function getValue(Sheet $sheet, $locale)
    {
        $event         = $sheet->getEvent();
        $dateFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            $event->getTimeZone()
        );

        $schedulePublishDate = $event->getConfiguration()->getSchedulePublishDate();

        if (null !== $schedulePublishDate) {
            $formattedDate = $dateFormatter->format($schedulePublishDate);
        }

        return isset($formattedDate) && false !== $formattedDate ? $formattedDate : '';
    }
}
