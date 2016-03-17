<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Operator;

use Doctrine\ORM\EntityRepository;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Event\EventEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('event', EventEntityType::class, [
                'label'         => false,
                'required'      => false,
                'expanded'      => false,
                'multiple'      => false,
                'placeholder'   => 'form.filter_operator.event.all',
                'query_builder' => function (EntityRepository $entityRepository) use ($options) {
                    return $entityRepository
                        ->createQueryBuilder('event')
                        ->where('event IN (:events)')
                        ->setParameter('events', $options['events']);
                },
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'form.filter_operator.children.submit.label',
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'events',
        ]);
        $resolver->setDefaults([
            'required'        => false,
            'method'          => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
