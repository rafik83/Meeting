<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Planning;

use Proximum\Vimeet\Application\Command\Product\Planning\CreatePlanning;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\AbstractCreateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreatePlanningType extends AbstractCreateType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('translations', CollectionType::class, [
                'entry_type' => TranslationsType::class,
                'label'      => false,
            ])
            ->add('quantityMax', IntegerType::class, [
                'required' => false,
                'attr'     => [
                    'min' => 0,
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => CreatePlanning::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_create_planning';
    }
}
