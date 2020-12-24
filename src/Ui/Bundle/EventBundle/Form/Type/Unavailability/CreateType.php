<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
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
use Proximum\Vimeet\Domain\Time\TimeRangeViewTransformer;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    public const MESSAGE_MAX_LENGTH = 150;

    /** @var ParticipantInfoGuesser */
    private $guesser;

    /** @var TranslatorAdapter */
    private $translator;

    public function __construct(ParticipantInfoGuesser $guesser, TranslatorAdapter $translator)
    {
        $this->guesser      = $guesser;
        $this->translator   = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Sheet $sheet */
        $sheet  = $options['sheet'];
        /** @var Event $event */
        $event  = $options['event'];
        $locale = $options['locale'];
        $timezone = $options['timezone'];

        $days = TimeRangeViewTransformer::fromEventDays($event->getDays(), $timezone);

        if (count($days) > 1) {
            $builder
                ->add('day', DayType::class, [
                    'days' => $days,
                    'formatter' => DayHelper::getFormatter($locale, $timezone),
                    'locale' => $locale,
                    'required' => true,
                ]);
        }

        $builder
            ->add('time', TimeRangeType::class, [
                'days' => $days,
                'label' => false,
                'timezone' => $timezone,
                'required' => true,
            ])
        ;

        if (true !== $options['isUserAloneParticipant']) {
            $builder
                ->add('participants', ChoiceType::class, [
                    'choices' => $sheet->getParticipants()->toArray(),
                    'choice_label' => function (Participant $participant) use ($locale) {
                        return $this->guesser->guessParticipantCompleteName($participant, $locale);
                    },
                    'expanded' => true,
                    'multiple' => true,
                ])
            ;
        }

        $builder
            ->add('message', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'data-text-max-length-indicator' => self::MESSAGE_MAX_LENGTH,
                    'data-text-max-length-translations' => sprintf(
                        '%s|%s|%s',
                        $this->translator->trans(
                            'form.create_unavailability.data.maxLength.translations.plural',
                            [],
                            'forms'
                        ),
                        $this->translator->trans(
                            'form.create_unavailability.data.maxLength.translations.singular',
                            [],
                            'forms'
                        ),
                        $this->translator->trans(
                            'form.create_unavailability.data.maxLength.translations.reached',
                            [],
                            'forms'
                        )
                    ),
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('event');
        $resolver->setRequired('timezone');
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
    public function getBlockPrefix(): string
    {
        return 'create_unavailability';
    }
}
