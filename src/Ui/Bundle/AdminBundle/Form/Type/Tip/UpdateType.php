<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip;

use Proximum\Vimeet\Application\Command\Tip\Update;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('onMeetingManagement', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onCatalog', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onPrintPlanning', CheckboxType::class, [
                'required' => false,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type'   => TipTranslationType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'label'        => false,
            ])
            ->add('submit', SubmitType::class);
    }
    
    /** {@inheritdoc} */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Update::class,
        ]);
    }
    
    /** {@inheritdoc} */
    public function getBlockPrefix()
    {
        return 'tip_update';
    }
}
