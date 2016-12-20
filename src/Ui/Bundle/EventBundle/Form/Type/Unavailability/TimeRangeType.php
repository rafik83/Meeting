<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TimeRangeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Event $event */
        $event = $options['event'];
        $days  = $event->getDays();

        if (!empty($days)) {
            $begin = null;
            $end   = null;

            foreach ($days as $day) {
                if ($begin === null || $begin->format('H') > $day->getStartTime()->format('H')) {
                    $begin = $day->getStartTime();
                }

                if ($end === null || $end->format('H') < $day->getEndTime()->format('H')) {
                    $end = $day->getEndTime();
                }
            }

            $hours = [];

            $begin->setTimezone(new \DateTimeZone($event->getTimeZone()));
            $end->setTimezone(new \DateTimeZone($event->getTimeZone()));

            $beginFormat = intval($begin->format('H'));
            $endFormat   = intval($end->format('H'));

            for ($hour = $beginFormat; $hour <= $endFormat; $hour++) {
                $hours[$hour] = $hour;
            }

            $builder
                ->add('begin', TimeType::class, [
                    'hours' => $hours
                ])
                ->add('end', TimeType::class, [
                    'hours' => $hours
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('event');
        $resolver->setAllowedTypes('event', Event::class);
    }
}
