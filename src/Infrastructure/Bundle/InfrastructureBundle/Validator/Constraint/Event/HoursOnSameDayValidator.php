<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Event;

use DateTimeZone;
use Proximum\Vimeet\Application\Command\Event\Day\Update;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HoursOnSameDayValidator extends ConstraintValidator
{
    /**
     * @param Update     $update
     * @param Constraint $constraint
     */
    public function validate($update, Constraint $constraint)
    {
        foreach ($update->days as $day) {
            $startTime = new \DateTime($day['startTime']->format('Y-m-d H:i:s'));
            $startTime->setTimezone(new DateTimeZone($update->event->getTimeZone()));

            $endTime = new \DateTime($day['endTime']->format('Y-m-d H:i:s'));
            $endTime->setTimezone(new DateTimeZone($update->event->getTimeZone()));

            if ($startTime->format('Y-m-d') !== $endTime->format('Y-m-d')) {
                $this
                    ->context
                    ->buildViolation('validators.schedule_day.shouldBeTheSameDay')
                    ->atPath('days')
                    ->addViolation();
            }
        }
    }
}
