<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package;

use Proximum\Vimeet\Application\Command\Package\Step\SelectParticipantAndPlanning;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Package\Planning\QuantityMaxGuesser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantAndPlanningType extends AbstractType
{
    /**
     * @var QuantityMaxGuesser
     */
    private $quantityMaxGuesser;

    /**
     * @param QuantityMaxGuesser $quantityMaxGuesser
     */
    public function __construct(QuantityMaxGuesser $quantityMaxGuesser)
    {
        $this->quantityMaxGuesser = $quantityMaxGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Sheet $sheet */
        $sheet = $options['sheet'];

        $builder->add('planningQuantity', TextType::class, [
            'label' => false,
            'attr'  => [
                'data-min' => 0,
                'data-max' => $this->quantityMaxGuesser->getMaxPlanning($sheet),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(['sheet']);
        $optionsResolver->addAllowedTypes('sheet', Sheet::class);
        $optionsResolver->setDefaults(
            [
                'data_class' => SelectParticipantAndPlanning::class,
            ]
        );
    }
}
