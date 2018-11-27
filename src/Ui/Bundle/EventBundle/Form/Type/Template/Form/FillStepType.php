<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Template\Form;

use Proximum\Vimeet\Application\View\Template\Form\BlockStepView;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Template\AbstractBlockType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FillStepType extends AbstractBlockType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired(['blockStepView'])
            ->setAllowedTypes('blockStepView', BlockStepView::class)
        ;

        $resolver->setDefaults([
            'data_class' => Block::class,
            'validation_groups' => ['Default', 'form_template_block_step'],
        ]);
    }

    protected function getObjects(array $options): array
    {
        /** @var BlockStepView $blockStepView */
        $blockStepView = $options['blockStepView'];

        return $blockStepView->block->getEditableObjects();
    }

    public function getBlockPrefix(): string
    {
        return 'fill_step_form_template';
    }
}
