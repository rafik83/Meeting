<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Application\Command\Event\ConfigureDates;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigureDatesType extends AbstractType
{
    private const CONFIGURATION_DATES = [
        'catalogOnlineDate',
        'happeningsOpenDate',
        'schedulePublishDate',
        'closeMeetingRequestDate',
        'closeAnsweringMeetingRequestDate',
        'smsActivationDate',
        'agendaOnlineDate',
        'registrationOpenDate',
        'registrationCloseDate',
        'enableBadgeForParticipantDate',
        'enableVisioTestMenuButtonDate',
    ];

    private const CONFIGURATION_DATES_HELP = [
        'enableBadgeForParticipantDate' => 'form.event_configure_date.children.enableBadgeForParticipantDate.help',
    ];

    private const CONFIGURATION_DATES_NETWORKING = [
        'networkingOpenDate',
        'networkingCloseDate',
    ];

    private const CONFIGURATION_DATES_CALL_VISIO = [
        'callVisioOpenDate',
        'callVisioCloseDate',
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
                    'help' => self::CONFIGURATION_DATES_HELP[$configurationDate] ?? null,
                ]);
        }

        if ($options['showDateNetworking']) {
            foreach (self::CONFIGURATION_DATES_NETWORKING as $configurationDateNetworking) {
                $builder
                    ->add($configurationDateNetworking, DateTimePickerType::class, [
                        'view_timezone' => $options['event']->getTimezone(),
                        'required' => false,
                    ]);
            }
        }

        if ($options['showDateCallVisio']) {
            foreach (self::CONFIGURATION_DATES_CALL_VISIO as $configurationDateCallVisio) {
                $builder
                    ->add($configurationDateCallVisio, DateTimePickerType::class, [
                        'view_timezone' => $options['event']->getTimezone(),
                        'required' => false,
                    ]);
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'showDateNetworking']);
        $resolver->setRequired(['event', 'showDateCallVisio']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('showDateNetworking', 'bool');
        $resolver->setAllowedTypes('showDateCallVisio', 'bool');
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
