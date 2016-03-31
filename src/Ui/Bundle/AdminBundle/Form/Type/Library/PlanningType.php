<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library;

use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType as CoreCheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as CoreChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class PlanningType extends AbstractLocalizedType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $template = $options['template'];
        $locale   = $options['locale'];
        $sheet    = $options['sheet'];

        $builder
            ->add('planning', CoreCheckboxType::class, [
                'label'    => $template['label'][$locale],
                'required' => isset($template['required']) ? $template['required'] : false,
            ])
            ->add('quantity', CoreChoiceType::class, [
                'choices'           => $this->getPlanningChoice($sheet),
                'label'             => false,
                'required'          => isset($template['required']) ? $template['required'] : false,
                'choices_as_values' => true,
            ]);
    }

    /**
     * @param Sheet $sheet
     *
     * @return array
     */
    private function getPlanningChoice(Sheet $sheet)
    {
        $maxPlanning    = $sheet->getType()->getMaxPlanning();
        $step           = 1;
        $planningBought = 0;

        if (!empty($sheet->getOrders())) {
            foreach ($sheet->getTypePackageTemplate() as $blockKey => $block) {
                foreach ($block['template'] as $productKey => $product) {
                    if (isset($product['type'])
                        && 'lib_planning' === $product['type']
                        && isset($sheet->getPackageData()[$blockKey][$productKey]['planning'])
                        && $sheet->getPackageData()[$blockKey][$productKey]['planning'] === true
                        && isset($sheet->getPackageData()[$blockKey][$productKey]['quantity'])
                    ) {
                        $planningBought = $sheet->getPackageData()[$blockKey][$productKey]['quantity'];
                    }
                }
            }
        }

        $range = range(1, $maxPlanning - $planningBought, $step);

        return array_combine($range, $range);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lib_planning';
    }
}
