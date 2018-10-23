<?php
/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) vimeet
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\StaticFormulation;

use Proximum\Vimeet\Application\Command\StaticFormulation\Update;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractStaticFormulationType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Update::class
        ]);
    }

    public function getBlockPrefix()
    {
        return 'static_formulation_update';
    }
}
