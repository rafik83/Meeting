<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Nomenclature;

use Proximum\Vimeet\Application\Command\Nomenclature\Update;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('sort', ChoiceType::class, [
                'expanded' => true,
                'choices'  => [
                    'form.nomenclature_update_type.children.sort.choice.sort_alpha' => true,
                    'form.nomenclature_update_type.children.sort.choice.sort_file'  => false,
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Update::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'nomenclature_update_type';
    }
}
