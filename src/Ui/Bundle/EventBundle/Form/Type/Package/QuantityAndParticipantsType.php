<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package;

use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\ParticipantChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuantityAndParticipantsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $isAttributable = (bool) $options['isAttributable'];

        if (!$isAttributable) {
            $builder
                ->add(
                    'quantity',
                    QuantityType::class,
                    [
                        'max' => $options['max'],
                        'minMessage' => $options['minMessage'],
                        'maxMessage' => $options['maxMessage'],
                    ]
                );
        } else {
            $builder
                ->add(
                    'participants',
                    ParticipantChoiceType::class,
                    [
                        'sheet' => $options['sheet'],
                        'locale' => $options['locale'],
                        'isMultiple' => true,
                        'isSelect2' => true,
                    ]
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(
            [
                'sheet',
                'locale',
                'max',
                'minMessage',
                'maxMessage',
                'isAttributable',
            ]
        );
        $optionsResolver->addAllowedTypes('sheet', Sheet::class);
        $optionsResolver->addAllowedTypes('isAttributable', 'boolean');
        $optionsResolver->setDefaults(
            [
                'data_class' => OptionRow::class,
            ]
        );
    }
}
