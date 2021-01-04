<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\ExtraParameter;

use Proximum\Vimeet\Domain\Event\ExtraParameter\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class CreateType extends AbstractType
{
    const TRANSLATION_TYPE = 'form.extra_parameter_create.children.type.choices';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'required' => true,
                'choices' => Type::TYPES,
                'choice_label' => function ($key) {
                    return sprintf('%s.%s', self::TRANSLATION_TYPE, $key);
                },
            ])
            ->add('name', TextType::class, [
                'required' => true,
            ])
            ->add('value', TextareaType::class, [
                'required' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'extra_parameter_create';
    }
}
