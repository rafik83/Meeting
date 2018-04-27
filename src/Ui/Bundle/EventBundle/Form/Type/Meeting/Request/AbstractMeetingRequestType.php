<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractMeetingRequestType extends AbstractType
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Sheet $sheet */
        $sheet = $options['sheet'];

        if ($options['show_description']) {
            $builder
                ->add('description', TextType::class, [
                    'placeholder' => $options['placeholder_description'],
                    'required'    => false,
                ])
            ;
        }

        if (1 < $sheet->countParticipant()) {
            $builder
                ->add('participants', ChoiceType::class, [
                    'choices'      => $sheet->getParticipants()->toArray(),
                    'choice_label' => function ($participant) use ($options) {
                        return $this->participantInfoGuesser
                            ->guessParticipantCompleteName($participant, $options['locale']);
                    },
                    'expanded'     => true,
                    'multiple'     => true,
                    'required'     => false,
                    'choice_attr'  => function () {
                        return ['class' => 'request-checkbox-select-participant'];
                    },
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['sheet', 'locale']);
        $resolver->setDefault('placeholder_description', '');
        $resolver->setDefault('show_description', true);
    }
}
