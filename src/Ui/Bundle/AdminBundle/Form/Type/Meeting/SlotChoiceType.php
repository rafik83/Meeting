<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Meeting;

use Proximum\Vimeet\Domain\Model\MeetingSlot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SlotChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['slots', 'timeZone', 'locale'])
            ->setAllowedTypes('slots', 'array')
            ->setAllowedTypes('locale', 'string')
            ->setAllowedTypes('timeZone', 'string')
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('slot', ChoiceType::class, [
                'choices' => $this->getFormattedSlots($options['slots'], $options['locale'], $options['timeZone']),
                'required' => true,
            ])
        ;
    }

    /**
     * @param MeetingSlot[] $slots
     * @param string        $locale
     * @param string        $timeZone
     *
     * @return array
     */
    private function getFormattedSlots(array $slots, string $locale, string $timeZone): array
    {
        $formattedSlots = [];

        $dayFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::NONE,
            $timeZone
        );

        $hourFormatter = \IntlDateFormatter::create(
            $locale,
            \IntlDateFormatter::NONE,
            \IntlDateFormatter::SHORT,
            $timeZone
        );

        foreach ($slots as $slot) {
            $formattedSlots[$dayFormatter->format($slot->getBegin())][$slot->getId()] = sprintf(
                '%s - %s',
                $hourFormatter->format($slot->getBegin()),
                $hourFormatter->format($slot->getEnd())
            );
        }

        return $formattedSlots;
    }
}
