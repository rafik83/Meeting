<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Plan;

use Proximum\Vimeet\Application\Command\Product\Plan\CreatePlan;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\AbstractCreateType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Feature\FeatureType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\ProductIncludedType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreatePlanType extends AbstractCreateType
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
            ->add('features', CollectionType::class, [
                'entry_type'    => FeatureType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
                'entry_options' => [
                    'label' => false,
                    'event' => $options['event'],
                ],
            ])
            ->add('file', FileType::class, [
                'required' => false,
            ])
            ->add('productIncluded', CollectionType::class, [
                'entry_type'    => ProductIncludedType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
                'entry_options' => [
                    'label'  => false,
                    'event'  => $options['event'],
                    'locale' => $options['locale'],
                ],
                'attr'          => [
                    'data-shared-choices-collection' => 'products',
                ],
            ])
            ->add('availabilityCurrent', IntegerType::class, [
                'required' => false,
                'attr'     => [
                    'min' => 0,
                ],
            ])
            ->add('availabilityMax', IntegerType::class, [
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
            'data_class' => CreatePlan::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_create_plan';
    }
}
