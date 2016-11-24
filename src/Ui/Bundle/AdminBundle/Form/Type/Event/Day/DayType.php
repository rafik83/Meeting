<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Day;

use Proximum\Vimeet\Application\Command\Event\Day\UpdateDay;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Transformer\DayStartTimeEndTimeToDayTransformer;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DayType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add('day', DateTimePickerType::class, [
                'format'        => 'd/m/Y',
                'display_hour'  => false,
                'view_timezone' => $event->getTimeZone(),
            ])
            ->add('startTime', DateTimePickerType::class, [
                'format'        => 'H:i',
                'display_date'  => false,
                'view_timezone' => $event->getTimeZone(),
            ])
            ->add('endTime', DateTimePickerType::class, [
                'format'        => 'H:i',
                'display_date'  => false,
                'view_timezone' => $event->getTimeZone(),
            ])
        ;
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
