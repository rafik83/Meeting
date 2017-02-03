<?php

/*
 * This file is part of the Proximum Vimeet website.
 *
 * Copyright © Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportedChoiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'placeholder'               => 'form.sheet_filter.children.imported.all.label',
            'choice_translation_domain' => 'messages',
            'choices'                   => [
                'event.sheet.imported.imported.label'                    => 'imported',
                'event.sheet.imported.imported_with_connection.label'    => 'imported_with_connection',
                'event.sheet.imported.imported_without_connection.label' => 'imported_without_connection',
                'event.sheet.imported.not_imported.label'                => 'not_imported',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'messaging_campaign_imported_choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
