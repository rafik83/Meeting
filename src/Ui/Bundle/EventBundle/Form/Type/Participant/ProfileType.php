<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant;

use Proximum\Vimeet\Domain\Template;
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

class ProfileType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Template\TemplateData $template */
        $template = $options['template'];

        foreach ($template->getProfileObjects() as $key => $object) {
            if ($object instanceof Template\TemplateObject\EditableText) {
                $this->addText($key, $builder, $object, $options['locale'], $options['locales']);
            } elseif ($object instanceof Template\TemplateObject\Gender) {
                $this->addGender($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\TemplateObject\Nomenclature) {
                $this->addNomenclature($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\TemplateObject\BooleanObject) {
                $this->addBoolean($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\TemplateObject\Telephone) {
                $this->addTelephone($key, $builder, $object, $options['locale'], $options['country']);
            } elseif ($object instanceof Template\TemplateObject\Country) {
                $this->addCountry($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\TemplateObject\Url) {
                $this->addUrl($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\TemplateObject\UploadObject) {
                $this->addFile($key, $builder, $object, $options['locale']);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'        => Template\TemplateData::class,
            'validation_groups' => ['Default', 'profile'],
        ]);
        $resolver->setRequired(['template', 'locale', 'country', 'locales']);
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setAllowedTypes('locales', 'array');
        $resolver->setAllowedTypes('country', 'string');
        $resolver->setAllowedTypes('template', Template\TemplateData::class);
    }

    /**
     * @param string                  $key
     * @param FormBuilderInterface    $builder
     * @param Template\TemplateObject $object
     * @param string                  $locale
     */
    private function addFile(string $key, FormBuilderInterface $builder, Template\TemplateObject $object, string $locale): void
    {
        $builder->add($key, FileDataType::class, [
            'showLabel' => true,
            'locale' => $locale,
            'object' => $object,
        ]);
    }

    /**
     * @param string                  $key
     * @param FormBuilderInterface    $builder
     * @param Template\TemplateObject $object
     * @param string                  $locale
     * @param array                   $locales
     */
    private function addText($key, FormBuilderInterface $builder, Template\TemplateObject $object, $locale, array $locales)
    {
        if ($object->isTranslatable()) {
            $builder->add($key, EditableTextTranslationType::class, [
                'locales' => $locales,
                'object'  => $object,
                'label'   => $object->getOption('label', $locale),
            ]);
        } else {
            $builder->add($key, EditableTextInputDataType::class, [
                'label'  => false,
                'locale' => $locale,
                'object' => $object,
            ]);
        }
    }

    /**
     * @param string                  $key
     * @param FormBuilderInterface    $builder
     * @param Template\TemplateObject $object
     * @param string                  $locale
     */
    private function addGender($key, FormBuilderInterface $builder, Template\TemplateObject $object, $locale)
    {
        $builder->add($key, GenderDataType::class, [
            'object' => $object,
            'locale' => $locale,
            'label'  => false,
        ]);
    }

    /**
     * @param string                  $key
     * @param FormBuilderInterface    $builder
     * @param Template\TemplateObject $object
     * @param string                  $locale
     */
    private function addBoolean(
        $key,
        FormBuilderInterface $builder,
        Template\TemplateObject $object,
        $locale
    ) {
        $builder->add($key, BooleanDataType::class, [
            'object'  => $object,
            'locale'  => $locale,
            'label'   => false,
        ]);
    }

    /**
     * @param string                               $key
     * @param FormBuilderInterface                 $builder
     * @param Template\TemplateObject\Nomenclature $object
     * @param string                               $locale
     */
    private function addNomenclature($key, FormBuilderInterface $builder, Template\TemplateObject\Nomenclature $object, $locale)
    {
        $builder->add($key, NomenclatureDataType::class, [
            'label'          => false,
            'locale'         => $locale,
            'object'         => $object,
            'placeholder'    => $object->getOption('label')[$locale],
            'selectMultiple' => true,
        ]);
    }

    /**
     * @param string                      $key
     * @param FormBuilderInterface        $builder
     * @param Template\TemplateObject\Url $object
     * @param string                      $locale
     */
    private function addUrl($key, FormBuilderInterface $builder, Template\TemplateObject\Url $object, $locale)
    {
        $builder->add($key, UrlDataType::class, [
            'label'  => false,
            'locale' => $locale,
            'object' => $object,
        ]);
    }

    /**
     * @param string                  $key
     * @param FormBuilderInterface    $builder
     * @param Template\TemplateObject $object
     * @param string                  $locale
     * @param string                  $country
     */
    private function addTelephone($key, FormBuilderInterface $builder, Template\TemplateObject $object, $locale, $country)
    {
        $builder->add($key, TelephoneDataType::class, [
            'country' => $country,
            'label'   => false,
            'locale'  => $locale,
            'object'  => $object,
        ]);
    }

    /**
     * @param string                  $key
     * @param FormBuilderInterface    $builder
     * @param Template\TemplateObject $object
     * @param string                  $locale
     */
    private function addCountry($key, FormBuilderInterface $builder, Template\TemplateObject $object, $locale)
    {
        $builder->add($key, CountryDataType::class, [
            'label'  => false,
            'locale' => $locale,
            'object' => $object,
        ]);
    }
}
