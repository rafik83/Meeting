<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant;

use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\CountryDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\EditableTextInputDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\NomenclatureDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\TelephoneDataType;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\UrlDataType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Template\TemplateData $template */
        $template = $options['template'];

        foreach ($template->getCompanyObjects() as $key => $object) {
            if ($object instanceof Template\Object\EditableText) {
                $this->addText($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\Object\Nomenclature) {
                $this->addNomenclature($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\Object\Telephone) {
                $this->addTelephone($key, $builder, $object, $options['locale'], $options['country']);
            } elseif ($object instanceof Template\Object\Url) {
                $this->addUrl($key, $builder, $object, $options['locale']);
            } elseif ($object instanceof Template\Object\Country) {
                $this->addCountry($key, $builder, $object, $options['locale']);
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
            'validation_groups' => ['Default', 'company']
        ]);
        $resolver->setRequired(['template', 'locale', 'country']);
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setAllowedTypes('template', Template\TemplateData::class);
        $resolver->setAllowedTypes('country', 'string');
    }

    /**
     * @param string               $key
     * @param FormBuilderInterface $builder
     * @param Template\Object      $object
     * @param string               $locale
     */
    private function addText($key, FormBuilderInterface $builder, Template\Object $object, $locale)
    {
        $builder->add($key, EditableTextInputDataType::class, [
            'locale' => $locale,
            'object' => $object,
        ]);
    }

    /**
     * @param string                       $key
     * @param FormBuilderInterface         $builder
     * @param Template\Object\Nomenclature $object
     * @param string                       $locale
     */
    private function addNomenclature($key, FormBuilderInterface $builder, Template\Object\Nomenclature $object, $locale)
    {
        $builder->add($key, NomenclatureDataType::class, [
            'locale'      => $locale,
            'object'      => $object,
            'placeholder' => $object->getOption('label')[$locale],
        ]);
    }

    /**
     * @param string               $key
     * @param FormBuilderInterface $builder
     * @param Template\Object\Url  $object
     * @param string               $locale
     */
    private function addUrl($key, FormBuilderInterface $builder, Template\Object\Url $object, $locale)
    {
        $builder->add($key, UrlDataType::class, [
            'locale' => $locale,
            'object' => $object,
        ]);
    }

    /**
     * @param string               $key
     * @param FormBuilderInterface $builder
     * @param Template\Object      $object
     * @param string               $locale
     * @param string               $country
     */
    private function addTelephone($key, FormBuilderInterface $builder, Template\Object $object, $locale, $country)
    {
        $builder->add($key, TelephoneDataType::class, [
            'locale'  => $locale,
            'object'  => $object,
            'country' => $country,
        ]);
    }

    /**
     * @param string               $key
     * @param FormBuilderInterface $builder
     * @param Template\Object      $object
     * @param string               $locale
     */
    private function addCountry($key, FormBuilderInterface $builder, Template\Object $object, $locale)
    {
        $builder->add($key, CountryDataType::class, [
            'locale' => $locale,
            'object' => $object,
        ]);
    }
}
