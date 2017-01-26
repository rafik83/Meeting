<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) 2017 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot;

use IntlDateFormatter;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\MeetingSlotRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnavailabilityType extends AbstractType
{
    /**
     * @var MeetingSlotRepositoryInterface
     */
    private $meetingSlotRepository;

    /**
     * BatchUnavailabilityType constructor.
     *
     * @param MeetingSlotRepositoryInterface $meetingSlotRepository
     */
    public function __construct(MeetingSlotRepositoryInterface $meetingSlotRepository)
    {
        $this->meetingSlotRepository = $meetingSlotRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Event $event */
        $event        = $options['event'];
        $meetingSlots = $this->meetingSlotRepository->findByEvent($options['event']);

        $dateFormatter = IntlDateFormatter::create(
            $options['locale'],
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE,
            $event->getTimeZone()
        );

        $timeFormatter = IntlDateFormatter::create(
            $options['locale'],
            IntlDateFormatter::NONE,
            IntlDateFormatter::SHORT,
            $event->getTimeZone()
        );

        foreach ($meetingSlots as $meetingSlot) {
            $builder
                ->add($meetingSlot->getId(), ChoiceType::class, [
                    'expanded' => true,
                    'multiple' => true,
                    'label'    => '',
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setRequired('event');
        $resolver->setRequired('locale');
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }
}
