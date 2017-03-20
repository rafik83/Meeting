<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Transaction;

use Proximum\Vimeet\Application\Command\Transaction\Filter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $firstDay = new \DateTime();
        $firstDay->modify('first day of last month');
        
        $lastDay = new \DateTime();
        $lastDay->modify('last day of last month + 23 hours');
        
        $builder
            ->add('beginDate', DateType::class, [
                'widget'      => 'single_text',
                'format'      => 'dd-MM-yyyy',
                'data'        => $firstDay,
                'placeholder' => 'form.transaction_find.children.date.placeholder',
            ])
            ->add('endDate', DateType::class, [
                'widget'      => 'single_text',
                'format'      => 'dd-MM-yyyy',
                'data'        => $lastDay,
                'placeholder' => 'form.transaction_find.children.date.placeholder',
            ])
            ->add('submit', SubmitType::class, [
                'attr' => [
                    'class' => 'pull-right col-md-2',
                ],
            ])
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
            'submit'     => true,
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'transaction_find';
    }
}
