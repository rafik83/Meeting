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
        'networkingOpenDate',
        'networkingCloseDate',
    ];

    private const CONFIGURATION_DATES_HELP = [
        'enableBadgeForParticipantDate' => 'form.event_configure_date.children.enableBadgeForParticipantDate.help',
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
