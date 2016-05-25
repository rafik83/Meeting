<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PurchasingFunnel;

use Proximum\Vimeet\Application\Command\PurchasingFunnel\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PurchasingFunnel\Model\OptionsType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PurchasingFunnel\Model\PackagesType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PurchasingFunnel\Model\ParticipantAndPlanningType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
            ->add('packages', PackagesType::class, [
                'event' => $options['event'],
                'label' => false,
            ])
            ->add('participantAndPlanning', ParticipantAndPlanningType::class, [
                'event' => $options['event'],
                'label' => false,
            ])
            ->add('options', OptionsType::class, [
                'event' => $options['event'],
                'label' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => Update::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'purchasing_funnel_update';
    }
}
