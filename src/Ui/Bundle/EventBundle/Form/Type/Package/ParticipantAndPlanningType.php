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
use Proximum\Vimeet\Domain\Repository\CartRowRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantAndPlanningType extends AbstractType
{
    /**
     * @var CartRowRepositoryInterface
     */
    private $cartRowRepository;

    /**
     * @param CartRowRepositoryInterface $cartRowRepository
     */
    public function __construct(CartRowRepositoryInterface $cartRowRepository)
    {
        $this->cartRowRepository = $cartRowRepository;
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
                'data-max' => $this->getAvailableAdditionalPlanningNumber($sheet),
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

    /**
     * @param Sheet $sheet
     *
     * @return int
     */
    private function getAvailableAdditionalPlanningNumber(Sheet $sheet)
    {
        $selectedPlan = $this->cartRowRepository->findCartRowPlanBySheet($sheet);

        $includedParticipantNumber  = 0;
        $includedParticipantProduct = $selectedPlan->getProduct()->getIncludedParticipantProduct();

        if ($includedParticipantProduct) {
            $includedParticipantNumber = $includedParticipantProduct->getQuantity();
        }

        return min(
            $sheet->getParticipants()->count() - $includedParticipantNumber,
            $sheet->getPackage()->getPlanning()->getQuantityMax()
        );
    }
}
