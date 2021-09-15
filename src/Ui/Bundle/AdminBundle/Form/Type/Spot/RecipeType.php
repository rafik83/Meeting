<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot;

use Proximum\Vimeet\Application\Components\Spot\Recipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prefix', TextType::class, ['required' => false])
            ->add('start', IntegerType::class, ['required' => false])
            ->add('end', IntegerType::class, ['required' => false])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
            'empty_data' => function (FormInterface $form) {
                return new Recipe(
                    $form->get('prefix')->getData(),
                    $form->get('start')->getData(),
                    $form->get('end')->getData()
                );
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'spot_reference_recipe';
    }
}
