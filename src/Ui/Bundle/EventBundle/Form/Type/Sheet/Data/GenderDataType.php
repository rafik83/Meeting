<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Domain\Template\TemplateObject\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GenderDataType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Gender $gender */
        $gender = $options['object'];
        $locale = $options['locale'];

        $builder
            ->add('gender', ChoiceType::class, [
                'choices'      => Gender::getGenders(),
                'choice_label' => function ($value) {
                    return sprintf('gender.%s', $value);
                },
                'placeholder'  => 'gender.none',
                'expanded'     => true,
                'multiple'     => false,
                'label'        => $gender->getOption('label', $locale),
                'required'     => $gender->getRequired(),
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', TemplateObject\Gender::class);
        $resolver->setDefaults([
            'label'      => true,
            'data_class' => TemplateObject\Gender::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'gender_data';
    }
}
