<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Planning;

use Proximum\Vimeet\Domain\Planning\PlanningOrderedBy;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportPlanningType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('types', TypeChoiceType::class, [
                'event'  => $options['event'],
                'user'   => $options['user'],
                'locale' => $options['locale'],
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('orderBy', ChoiceType::class, [
                'choices'            => PlanningOrderedBy::getPlanningOrderByOptions(),
                'choice_label'       => function ($label) {
                    return sprintf('form.admin_export_planning.children.orderBy.choice.%s', $label);
                },
                'translation_domain' => 'forms'
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'user', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'admin_export_planning';
    }
}
