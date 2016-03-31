<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddProductsByStepType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $template = $options['productTemplate'];

        foreach ($template->getSteps() as $step) {
            if (false === $step->hasProducts()) {
                continue;
            }

            $builder
                ->add($step->getKey(), DataProductType::class, [
                    'template' => $options['packageTemplate'][$step->getKey()]['template'],
                    'locale'   => $options['locale'],
                    'sheet'    => $options['sheet'],
                    'cart'     => $options['cart'],
                    'step'     => $step,
                    'label'    => false,
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['productTemplate', 'packageTemplate', 'locale', 'cart']);
        $resolver->setDefined(['sheet']);
    }
}
