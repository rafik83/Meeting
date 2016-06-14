<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Content;

use Proximum\Vimeet\Domain\Model\Event\Content;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentTranslationLocaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Content $content */
        $content = $options['content'];

        foreach ($content->getEvent()->getLocales() as $locale) {
            $builder
                ->add($locale, ContentTranslationType::class, [
                    'label'              => ucfirst(Intl::getLocaleBundle()->getLocaleName($locale)),
                    'translation_domain' => false,
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['content']);
        $resolver->setAllowedTypes('content', Content::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'content_translation_locale';
    }
}
