<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TipType extends AbstractType
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
            ->add('onSheet', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onProgram', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onAgenda', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onPackage', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onContacts', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onConfirmationPhone', CheckboxType::class, [
                'required' => false,
            ])
            ->add('onNetworking', CheckboxType::class, [
                'required' => false,
            ])
            ->add('translations', CollectionType::class, [
                'entry_type'    => TipTranslationType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
                'label'         => false,
            ])
        ;
    }
}
