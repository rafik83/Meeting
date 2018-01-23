<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Proximum\Vimeet\Application\Command\Event\ConfigureDates;

class ConfigureDatesType extends AbstractType
{
    const CONFIGURATION_DATES = [
        'catalogOnlineDate',
        'happeningsOpenDate',
        'schedulePublishDate',
        'closeMeetingRequestDate',
        'closeAnsweringMeetingRequestDate',
        'smsActivationDate',
        'agendaOnlineDate',
        'registrationOpenDate',
        'registrationCloseDate',
    ];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach (self::CONFIGURATION_DATES as $configurationDate) {
            $builder
                ->add($configurationDate, DateTimePickerType::class, [
                    'view_timezone' => $options['event']->getTimezone(),
                    'required' => false,
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => ConfigureDates::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'event_configure_date';
    }
}
