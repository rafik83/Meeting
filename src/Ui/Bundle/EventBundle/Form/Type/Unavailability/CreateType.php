<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Unavailability;

use Proximum\Vimeet\Application\Command\Unavailability\Create;
use Proximum\Vimeet\Domain\Event\Day\DayHelper;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $guesser;

    /**
     * @param ParticipantInfoGuesser $guesser
     */
    public function __construct(ParticipantInfoGuesser $guesser)
    {
        $this->guesser = $guesser;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Sheet $sheet */
        $sheet  = $options['sheet'];
        /** @var Event $event */
        $event  = $options['event'];
        $locale = $options['locale'];

        if (count($event->getDays()) > 1) {
            $builder
                ->add('day', DayType::class, [
                    'event'     => $event,
                    'formatter' => DayHelper::getFormatter($locale, $event->getTimeZone()),
                    'locale'    => $locale,
                    'required'  => true,
                ]);
        }

        $builder
            ->add('time', TimeRangeType::class, [
                'event'    => $event,
                'label'    => false,
                'required' => true,
            ])
        ;

        if ($options['isUserAloneParticipant'] !== true) {
            $builder
                ->add('participants', ChoiceType::class, [
                    'choices'      => $sheet->getParticipants()->toArray(),
                    'choice_label' => function (Participant $participant) use ($locale) {
                        return $this->guesser->guessParticipantCompleteName($participant, $locale);
                    },
                    'expanded'     => true,
                    'multiple'     => true,
                ])
            ;
        }

        $builder
            ->add('message', TextareaType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('event');
        $resolver->setRequired('locale');
        $resolver->setRequired('isUserAloneParticipant');
        $resolver->setRequired('sheet');
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('sheet', Sheet::class);
        $resolver->setDefault('data_class', Create::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'create_unavailability';
    }
}
