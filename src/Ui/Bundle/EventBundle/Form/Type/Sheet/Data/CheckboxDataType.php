<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CheckboxDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var TemplateObject\CheckboxObject $checkboxObject */
        $checkboxObject = $options['object'];
        $locale = $options['locale'];

        $builder
            ->add(
                'contentValue',
                CheckboxType::class,
                [
                    'label' => $checkboxObject->getOption('label', $locale),
                    'required' => $checkboxObject->getRequired(),
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', TemplateObject\CheckboxObject::class);
        $resolver->setDefaults(
            [
                'label' => true,
                'data_class' => TemplateObject\CheckboxObject::class,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'checkbox_object_data';
    }
}
