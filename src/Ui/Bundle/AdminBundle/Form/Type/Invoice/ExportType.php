<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Invoice;

use Proximum\Vimeet\Application\Command\Invoice\Export;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $firstDay = new \DateTime();
        $firstDay->modify('first day of last month')
        ->setTime(0, 0, 0);

        $lastDay = new \DateTime();
        $lastDay->modify('last day of last month')
        ->setTime(23, 59, 59);

        $builder
            ->add('beginDate', DateType::class, [
                'widget'      => 'single_text',
                'format'      => 'dd-MM-yyyy',
                'data'        => $firstDay,
                'placeholder' => 'form.invoice_export.children.date.placeholder',
            ])
            ->add('endDate', DateType::class, [
                'widget'      => 'single_text',
                'format'      => 'dd-MM-yyyy',
                'data'        => $lastDay,
                'placeholder' => 'form.invoice_export.children.date.placeholder',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'form.invoice_export.children.submit.label',
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
            'data_class'      => Export::class,
            'submit'          => true,
            'csrf_protection' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'invoice_export';
    }
}
