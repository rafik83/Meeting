<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Application\Command\Meeting\ApproveRequest;
use Proximum\Vimeet\Application\Components\Participant\ParticipantInfoGuesser;
use Proximum\Vimeet\Domain\Model\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingRequestApproveType extends AbstractType
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
        $builder
            ->add('toParticipants', ChoiceType::class, [
                'choices'           => $options['sheet']->getParticipants(),
                'expanded'          => true,
                'multiple'          => true,
                'required'          => false,
                'choices_as_values' => true,
                'choice_label'      => function (Participant $participant) {
                    return $this->participantInfoGuesser->guessParticipantInfo($participant);
                },
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['sheet']);
        $resolver->setDefaults([
            'data_class' => ApproveRequest::class,
            'submit'     => true,
        ]);
    }
}
