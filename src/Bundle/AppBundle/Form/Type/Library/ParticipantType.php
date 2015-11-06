<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library;

use Proximum\Vimeet\Bundle\AppBundle\Service\ParticipantManager;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\Form\FormBuilderInterface;

class ParticipantType extends AbstractLocalizedType
{
    /**
     * @var ParticipantManager
     */
    private $participantManager;

    /**
     * @param ParticipantManager $participantManager
     */
    public function __construct(ParticipantManager $participantManager)
    {
        $this->participantManager = $participantManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $template = $options['template'];
        $locale   = $options['locale'];
        $sheet    = $options['sheet'];

        $builder
            ->add('participant', 'checkbox', [
                'label'    => $template['label'][$locale],
                'required' => false,
                'required' => isset($template['required']) ? $template['required'] : false,
            ])
            ->add('participant_bought', 'choice', [
                'data'     => '',
                'choices'  => $this->getParticipantChoices($sheet),
                'label'    => false,
                'required' => isset($template['required']) ? $template['required'] : false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'lib_participant';
    }

    /**
     * @param Sheet $sheet
     *
     * @return array
     */
    private function getParticipantChoices(Sheet $sheet)
    {
        $remaining = $this->participantManager->getRemainingPossibleParticipantToBuy($sheet);
        $range     = range(0, $remaining, 1);

        return array_combine($range, $range);
    }
}
