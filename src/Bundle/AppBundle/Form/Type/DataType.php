<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Bundle\AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Proximum\Vimeet\Bundle\AppBundle\Form\Type\Library;

class DataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $types = [
            'choice_with_description' => Library\ChoiceWithDescriptionType::class,
            'upload_with_choices'     => Library\UploadWithChoicesType::class,
            'lib_text'                => Library\TextType::class,
            'lib_textarea'            => Library\TextareaType::class,
            'lib_email'               => Library\EmailType::class,
            'lib_option'              => Library\OptionType::class,
            'lib_participant'         => Library\ParticipantType::class,
            'lib_planning'            => Library\PlanningType::class,
            'lib_country'             => Library\CountryType::class,
            'lib_choice'              => Library\ChoiceType::class,
            'lib_last_name'           => Library\LastNameType::class,
            'lib_first_name'          => Library\FirstNameType::class,
            'lib_organisation'        => Library\OrganisationType::class,
        ];

        $template = $options['template'];
        $locale   = $options['locale'];
        $sheet    = $options['sheet'];

        foreach ($template as $i => $field) {
            if (!isset($types[$field['type']])) {
                throw new \RuntimeException('Type not found.');
            }

            $builder->add($i, $types[$field['type']], [
                'label'    => $field['label'][$locale],
                'help'     => isset($field['private']) && $field['private'] === true ? 'form.field.private' : null,
                'required' => isset($field['required']) && $field['required'] === true,
                'template' => $field,
                'locale'   => $locale,
                'sheet'    => $sheet,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['template', 'locale']);
        $resolver->setAllowedTypes('template', ['array']);
        $resolver->setAllowedTypes('locale', ['string']);
        $resolver->setDefaults(['sheet' => null]);
    }
}
