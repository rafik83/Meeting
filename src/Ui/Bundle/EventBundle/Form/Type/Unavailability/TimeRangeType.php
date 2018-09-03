<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
        $timezone = $options['timezone'];

        if (!empty($days)) {
            $begins = array_map(function (Event\Day $day) use ($timezone) {
                $clone = $day->getStartTime();
                $clone->setTimezone(new \DateTimeZone($timezone));

                return $clone;
            }, $days);

            usort($begins, function (\DateTime $one, \DateTime $another) {
                return intval($one->format('H')) - intval($another->format('H'));
            });

            $ends = array_map(function (Event\Day $day) use ($timezone) {
                $clone = $day->getEndTime();
                $clone->setTimezone(new \DateTimeZone($timezone));

                return $clone;
            }, $days);

            usort($ends, function (\DateTime $one, \DateTime $another) {
                return  intval($another->format('H')) - intval($one->format('H'));
            });

            $beginHour = $begins[0]->format('H');
            $endHour   = $ends[0]->format('H');
            $hours     = range($beginHour, $endHour);

            $builder
                ->add('begin', TimeType::class, [
                    'hours' => $hours,
                ])
                ->add('end', TimeType::class, [
                    'hours' => $hours,
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
        $resolver->setRequired('timezone');
        $resolver->setAllowedTypes('event', Event::class);
    }
}
