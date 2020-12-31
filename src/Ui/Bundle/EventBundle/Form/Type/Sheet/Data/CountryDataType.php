<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $country = $options['object'];
        $locale  = $options['locale'];

        $builder
            ->add('country', CountryType::class, [
                'label'              => $country->getOption('label', $locale),
                'required'           => $country->getOption('required'),
                'placeholder'        => $country->getOption('placeholder')[$locale],
                'attr'               => [
                    'class'            => 'form-control select2',
                    'data-placeholder' => $country->getOption('label')[$locale],
                ],
                'translation_domain' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', TemplateObject\Country::class);
        $resolver->setDefaults([
            'data_class' => TemplateObject\Country::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'country_data';
    }
}
