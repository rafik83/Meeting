<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Participant;


use Proximum\Vimeet\Application\Command\Product\Participant\UpdateParticipant;
use Proximum\Vimeet\Domain\Model\AvailabilityTimeRange;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\AbstractUpdateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateParticipantType extends AbstractUpdateType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('quantityMax', IntegerType::class, [
                'required' => false,
                'attr'     => [
                    'min' => 0,
                ],
            ])
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationsType::class,
                'label'      => false,
            ])
        ;

        if (!empty($options['availabilityTimeRanges'])) {
            $formatter = new \IntlDateFormatter(
                $options['locale'],
                \IntlDateFormatter::SHORT,
                \IntlDateFormatter::SHORT,
                $options['event']->getTimeZone()
            );

            $builder
                ->add('availabilityTimeRanges', ChoiceType::class, [
                    'required'     => false,
                    'select2'      => true,
                    'choice_label' => function (AvailabilityTimeRange $availabilityTimeRange = null) use ($formatter) {
                        if (null === $availabilityTimeRange) {
                            return '';
                        }

                        return sprintf(
                            '%s (%s - %s)',
                            $availabilityTimeRange->getName(),
                            $formatter->format($availabilityTimeRange->getBegin()),
                            $formatter->format($availabilityTimeRange->getEnd())
                        );
                    },
                    'choices' => $options['availabilityTimeRanges'],
                    'multiple' => true,
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired([
            'event',
            'locale',
            'availabilityTimeRanges'
        ]);
        $resolver->setDefaults([
            'data_class' => UpdateParticipant::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_update_participant';
    }
}
