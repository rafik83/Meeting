<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Participant;


use Proximum\Vimeet\Application\Command\Product\Participant\UpdateParticipant;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\AbstractUpdateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateParticipantType extends AbstractUpdateType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('quantityMax', IntegerType::class, [
                'required' => false,
                'attr'     => [
                    'min' => 0,
                ],
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationsType::class,
                'label'      => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => UpdateParticipant::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_update_participant';
    }
}
