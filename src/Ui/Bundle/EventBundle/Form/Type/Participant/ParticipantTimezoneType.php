<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant;

use Proximum\Vimeet\Application\Command\Participant\ParticipantTimezone;
use Proximum\Vimeet\Domain\Event\GetTimezoneHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantTimezoneType extends AbstractType
{
    /** @var GetTimezoneHelper */
    private $getTimezoneHelper;

    public function __construct(GetTimezoneHelper $getTimezoneHelper)
    {
        $this->getTimezoneHelper = $getTimezoneHelper;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('timezone', TimezoneType::class, [
            'choice_label' => function (string $key) {
                return $this->getTimezoneHelper->getTimezoneTranslated($key);
            },
            'attr' => [
                'class' => 'select2',
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ParticipantTimezone::class,
        ]);
    }
}
