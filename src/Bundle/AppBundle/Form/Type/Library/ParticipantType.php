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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType as CoreCheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as CoreChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

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
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);

        $view->vars['boughtParticipant'] = $this->participantManager->getAddedBoughtParticipant($options['sheet']);
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
            ->add('participant', CoreCheckboxType::class, [
                'label'    => $template['label'][$locale],
                'required' => isset($template['required']) ? $template['required'] : false,
            ])
            ->add('participant_bought', CoreChoiceType::class, [
                'choices'           => $this->getParticipantChoices($sheet),
                'label'             => false,
                'required'          => isset($template['required']) ? $template['required'] : false,
                'choices_as_values' => true,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
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
        $participantManager = $this->participantManager;
        $remaining          = $participantManager->getBuyQuantityParticipant($sheet);
        $added              = $participantManager->getAddedBoughtParticipant($sheet);

        if ($remaining === 0) {
            return [];
        }

        if ($added === 0) {
            $added = 1;
        }

        $range = range($added, $remaining, 1);

        return array_combine($range, $range);
    }
}
