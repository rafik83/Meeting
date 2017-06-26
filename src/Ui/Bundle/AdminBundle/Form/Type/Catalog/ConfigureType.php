<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Catalog;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event\TypeCheckboxType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('catalogPublic', CheckboxType::class)
            ->add('types', TypeCheckboxType::class, ['typeViews' => $options['typeViews']])
            ->add('categories', CheckboxType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {

    }

}
