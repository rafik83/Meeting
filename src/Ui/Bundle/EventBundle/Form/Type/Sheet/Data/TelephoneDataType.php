<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Library\TelephoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TelephoneDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $telephone = $options['object'];
        $locale    = $options['locale'];

        $builder
            ->add('telephone', TelephoneType::class, [
                'label'              => $telephone->getOption('label', $locale),
                'required'           => $telephone->getOption('required'),
                'placeholder'        => $telephone->getOption('placeholder')[$locale],
                'country'            => $options['country'],
                'translation_domain' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale', 'country']);
        $resolver->setAllowedTypes('object', TemplateObject\Telephone::class);
        $resolver->setDefaults([
            'data_class' => TemplateObject\Telephone::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'telephone_data';
    }
}
