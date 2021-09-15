<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TranslationsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['locales'] as $locale) {
            $builder->add($locale, $options['entry_type'], array_merge([
                'label'          => ucfirst(Intl::getLocaleBundle()->getLocaleName($locale)),
                'error_bubbling' => false,
            ], $options['entry_options']));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locales', 'entry_type']);
        $resolver->setDefault('entry_options', []);
        $resolver->setAllowedTypes('locales', 'array');
        $resolver->setAllowedTypes('entry_type', 'string');
        $resolver->setAllowedTypes('entry_options', 'array');
    }
}
