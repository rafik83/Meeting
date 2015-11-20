<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Unavailability;

use Doctrine\ORM\EntityRepository;
use Proximum\Vimeet\Application\Command\Unavailability\AddUnavailability;
use Proximum\Vimeet\Domain\Model\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddUnavailabilityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from', 'time', [
                'input'         => 'datetime',
                'widget'        => 'choice',
                'view_timezone' => 'Europe/Paris',
            ])
            ->add('to', 'time', [
                'input'         => 'datetime',
                'widget'        => 'choice',
                'view_timezone' => 'Europe/Paris',
            ])
            ->add('participants', 'entity', [
                'class' => Participant::class,
                'query_builder' => function (EntityRepository $entityRepository) use ($options) {
                    return $entityRepository
                        ->createQueryBuilder('participant')
                        ->where('participant.sheet = :sheet')
                        ->setParameter('sheet', $options['sheet']);
                },
                'choice_label' => function (Participant $participant) {
                    return $participant->getId();
                },
                'multiple'   => true,
                'expanded'   => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'sheet'
        ]);

        $resolver->setDefaults([
            'data_class' => AddUnavailability::class,
            'intention'  => 'add_unavailability',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'add_unavailability';
    }
}
