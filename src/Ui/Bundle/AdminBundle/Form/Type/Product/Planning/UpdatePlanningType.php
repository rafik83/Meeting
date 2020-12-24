<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Planning;

use Proximum\Vimeet\Application\Command\Product\Planning\UpdatePlanning;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\AbstractUpdateType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdatePlanningType extends AbstractUpdateType
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
            'data_class' => UpdatePlanning::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'product_update_planning';
    }
}
