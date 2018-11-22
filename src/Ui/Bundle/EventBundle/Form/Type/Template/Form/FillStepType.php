<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Template\Form;

use Proximum\Vimeet\Application\Command\Template\Form\FillStepCommand;
use Proximum\Vimeet\Application\View\Template\Form\BlockStepView;
use Proximum\Vimeet\Domain\Template\Block;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\BooleanDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\CountryDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\EditableTextInputDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\EditableTextTranslationType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\FileDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\GenderDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\NomenclatureDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\TelephoneDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\UrlDataType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FillStepType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var BlockStepView $blockStepView */
        $blockStepView = $options['blockStepView'];

        foreach ($blockStepView->block->getEditableObjects() as $key => $object) {
            if ($object instanceof TemplateObject\EditableText) {
                $this->addText($key, $builder, $object, $options['locale'], $options['locales']);
                continue;
            }

            if ($object instanceof TemplateObject\Gender) {
                $this->addGender($key, $builder, $object, $options['locale']);
                continue;
            }

            if ($object instanceof TemplateObject\Nomenclature) {
                $this->addNomenclature($key, $builder, $object, $options['locale']);
                continue;
            }

            if ($object instanceof TemplateObject\BooleanObject) {
                $this->addBoolean($key, $builder, $object, $options['locale']);
                continue;
            }

            if ($object instanceof TemplateObject\Telephone) {
                $this->addTelephone($key, $builder, $object, $options['locale'], $options['country']);
                continue;
            }

            if ($object instanceof TemplateObject\Country) {
                $this->addCountry($key, $builder, $object, $options['locale']);
                continue;
            }

            if ($object instanceof TemplateObject\Url) {
                $this->addUrl($key, $builder, $object, $options['locale']);
                continue;
            }

            if ($object instanceof TemplateObject\UploadObject) {
                $this->addFile($key, $builder, $object, $options['locale']);
                continue;
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(['blockStepView', 'locale', 'country', 'locales'])
            ->setAllowedTypes('locale', 'string')
            ->setAllowedTypes('locales', 'array')
            ->setAllowedTypes('country', 'string')
            ->setAllowedTypes('blockStepView', BlockStepView::class)
        ;

        $resolver->setDefaults([
            'data_class' => Block::class,
            'validation_groups' => ['Default', 'form_template_block_step'],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'fill_step_form_template';
    }

    private function addFile(
        string $key,
        FormBuilderInterface $builder,
        TemplateObject\UploadObject $object,
        string $locale
    ): void {
        $builder->add($key, FileDataType::class, [
            'showLabel' => true,
            'locale' => $locale,
            'object' => $object,
        ]);
    }

    private function addText(
        string $key,
        FormBuilderInterface $builder,
        TemplateObject\EditableText $object,
        string $locale,
        array $locales
    ): void {
        if ($object->isTranslatable()) {
            $builder->add($key, EditableTextTranslationType::class, [
                'locales' => $locales,
                'object' => $object,
                'label' => $object->getOption('label', $locale),
            ]);
        } else {
            $builder->add($key, EditableTextInputDataType::class, [
                'label'  => false,
                'locale' => $locale,
                'object' => $object,
            ]);
        }
    }

    private function addGender(
        string $key,
        FormBuilderInterface $builder,
        TemplateObject\Gender $object,
        string $locale
    ): void {
        $builder->add($key, GenderDataType::class, [
            'object' => $object,
            'locale' => $locale,
            'label' => false,
        ]);
    }

    private function addBoolean(
        string $key,
        FormBuilderInterface $builder,
        TemplateObject\BooleanObject $object,
        string $locale
    ): void {
        $builder->add($key, BooleanDataType::class, [
            'object' => $object,
            'locale' => $locale,
            'label' => false,
        ]);
    }

    private function addNomenclature(
        string $key,
        FormBuilderInterface $builder,
        TemplateObject\Nomenclature $object,
        string $locale
    ): void {
        $builder->add($key, NomenclatureDataType::class, [
            'label' => false,
            'locale' => $locale,
            'object' => $object,
            'placeholder' => $object->getOption('label')[$locale],
            'onMultipleUseSinglesInsteadOfCheckboxes' => true,
        ]);
    }

    private function addUrl(
        string $key,
        FormBuilderInterface $builder,
        TemplateObject\Url $object,
        string $locale
    ): void {
        $builder->add($key, UrlDataType::class, [
            'label' => false,
            'locale' => $locale,
            'object' => $object,
        ]);
    }

    private function addTelephone(
        string $key,
        FormBuilderInterface $builder,
        TemplateObject\Telephone $object,
        string $locale,
        string $country
    ): void {
        $builder->add($key, TelephoneDataType::class, [
            'country' => $country,
            'label' => false,
            'locale' => $locale,
            'object' => $object,
        ]);
    }

    private function addCountry(
        string $key,
        FormBuilderInterface $builder,
        TemplateObject\Country $object,
        string $locale
    ): void {
        $builder->add($key, CountryDataType::class, [
            'label' => false,
            'locale' => $locale,
            'object' => $object,
        ]);
    }
}
