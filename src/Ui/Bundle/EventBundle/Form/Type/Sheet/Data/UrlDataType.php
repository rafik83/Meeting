<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UrlDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $url    = $options['object'];
        $locale = $options['locale'];

        $builder
            ->add('url', UrlType::class, [
                'label'              => $url->getOption('label', $locale),
                'required'           => $url->getOption('required'),
                'placeholder'        => $url->getOption('placeholder')[$locale],
                'translation_domain' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', TemplateObject\Url::class);
        $resolver->setDefaults([
            'data_class' => TemplateObject\Url::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'url_data';
    }
}
