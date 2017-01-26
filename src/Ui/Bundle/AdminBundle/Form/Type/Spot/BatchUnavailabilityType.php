<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot;

use Proximum\Vimeet\Application\Command\Spot\UnavailabilityBatch;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchUnavailabilityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('spotUnavailabilities', UnavailabilityType::class, [
            'event' => $options['event'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UnavailabilityBatch::class,
            'submit'     => true,
        ]);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setRequired('event');

    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'spot_batch_unavailability';
    }
}
