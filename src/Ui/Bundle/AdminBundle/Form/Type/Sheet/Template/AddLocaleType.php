<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddLocaleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locales = array_filter(array_flip(Intl::getLocaleBundle()->getLocaleNames()), function ($locale) use ($options) {
            return !in_array($locale, $options['template']->getLocales());
        });

        $builder
            ->add('locale', LocaleType::class, ['choices' => $locales])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['template']);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_template_add_locale';
    }
}
