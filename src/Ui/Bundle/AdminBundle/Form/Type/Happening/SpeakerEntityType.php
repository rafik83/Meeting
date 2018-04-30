<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Happening;

use Doctrine\ORM\EntityRepository;
use Proximum\Vimeet\Domain\Model\Happening\Speaker;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpeakerEntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setDefaults([
            'class'        => Speaker::class,
            'query_builder' => function (Options $options) {
                return function (EntityRepository $entityRepository) use ($options) {
                    return $entityRepository
                        ->createQueryBuilder('speaker')
                        ->where('speaker.event = :event')
                        ->setParameter('event', $options['event'])
                        ->orderBy('speaker.lastname', 'asc')
                        ->addOrderBy('speaker.firstname', 'asc');
                };
            },
            'choice_label' => 'name',
        ]);
    }

    public function getParent()
    {
        return EntityType::class;
    }
}
