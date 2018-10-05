<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\ChangeOwner;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeOwnerType extends AbstractType
{
    /** @var ParticipantInfoGuesser */
    private $guesser;

    public function __construct(ParticipantInfoGuesser $guesser)
    {
        $this->guesser = $guesser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Sheet $sheet */
        $sheet = $options['sheet'];
        $locale = $options['locale'];

        $builder
            ->add('owner', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => $sheet->getParticipantsArray(),
                'choice_label' => function (Participant $participant) use ($locale) {
                    return $this->guesser->guessParticipantCompleteName($participant, $locale);
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('sheet');
        $resolver->setDefined('locale');
        $resolver->setAllowedTypes('sheet', Sheet::class);
        $resolver->setDefaults([
            'data_class' => ChangeOwner::class,
        ]);
    }
}
