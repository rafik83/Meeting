<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Template;

use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\EditableTextInputDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\NomenclatureDataType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlockObjectsCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Template\Block $block */
        $block = $options['block'];

        foreach ($block->getEditableObjects() as $uid => $object) {
            if ($object instanceof Template\TemplateObject\EditableText) {
                $this->addText($uid, $builder, $object, $options['locale']);
                continue;
            }

            if ($object instanceof Template\TemplateObject\Nomenclature) {
                $this->addNomenclature($uid, $builder, $object, $options['locale']);
                continue;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['block', 'locale']);
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setAllowedTypes('block', Template\Block::class);
    }

    private function addText(
        string $uid,
        FormBuilderInterface $builder,
        Template\TemplateObject\EditableText $object,
        string $locale
    ) {
        $builder->add(
            $uid,
            EditableTextInputDataType::class,
            [
                'label' => false,
                'object' => $object,
                'locale' => $locale,
                'data_class' => null,
            ]
        );
    }

    private function addNomenclature(
        string $uid,
        FormBuilderInterface $builder,
        Template\TemplateObject\Nomenclature $object,
        string $locale
    ) {
        $builder->add(
            $uid,
            NomenclatureDataType::class,
            [
                'label' => false,
                'locale' => $locale,
                'object' => $object,
                'placeholder' => $object->getLabel($locale),
                'onMultipleUseSinglesInsteadOfCheckboxes' => true,
                'data_class' => null,
            ]
        );
    }
}
