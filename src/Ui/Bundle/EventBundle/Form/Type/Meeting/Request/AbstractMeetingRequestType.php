<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Domain\Model\Participant;
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

        $builder
            ->add('description', TextType::class, [
                'required' => false,
            ])
        ;

        if (1 < $sheet->countParticipant()) {
            $builder
                ->add('participants', ChoiceType::class, [
                    'choices'      => array_merge($sheet->getParticipants()->toArray(), [null => null]),
                    'choice_label' => function ($participant) use ($options) {
                        if ($participant instanceof  Participant) {
                            return $this->participantInfoGuesser
                                ->guessParticipantCompleteName($participant, $options['locale']);
                        } else {
                            return 'form.catalog_create_meeting_request.children.participants.default.no_preference';
                        }
                    },
                    'expanded' => true,
                    'multiple' => true,
                    'required' => false,
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
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'catalog_create_meeting_request';
    }
}
