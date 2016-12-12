<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Happening;

use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Domain\Model\Happening;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('participants', ChoiceType::class, [
                'choices'  => $options['participants'],
                'multiple' => true,
                'expanded' => true,
            ])
        ;
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'happening',
            'participants',
        ]);

        $resolver->setAllowedTypes('happening', Happening::class);

        $resolver->setDefaults([
            'data_class'    => Participate::class,
            'csrf_token_id' => 'participate_happening',
        ]);
    }
}
