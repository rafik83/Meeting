<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Sheet;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BuyParticipantType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('participantData', new BuyAddParticipantType(), [
                'template' => $options['template'],
                'locale'   => $options['locale'],
                'label'    => false,
            ])
            ->add('participantBuyOption', new BuyParticipantOptionType(), [
                'template' => $options['template'],
                'locale'   => $options['locale'],
                'required' => false,
                'label'    => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Proximum\Vimeet\Application\Command\Sheet\BuyParticipant',
            'intention'  => 'buy_participant',
        ]);

        $resolver->setRequired(['template', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'buy_participant';
    }
}
