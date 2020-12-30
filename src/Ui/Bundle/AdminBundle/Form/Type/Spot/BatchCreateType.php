<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot;

use Proximum\Vimeet\Application\Command\Spot\BatchCreate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchCreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recipes', CollectionType::class, [
                'entry_type'    => RecipeType::class,
                'entry_options' => ['label' => false],
                'allow_add'     => true,
                'allow_delete'  => true,
            ])
            ->add('size', NumberType::class, [
                'required' => true,
            ])
            ->add('meetingCapacity', IntegerType::class, [
                'required' => true,
            ])
            ->add('seatCapacity', IntegerType::class, [
                'required' => true,
            ])
            ->add('active', CheckboxType::class, [
                'required' => false,
            ])
            ->add('priority', IntegerType::class, [
                'required' => true,
            ])
            ->add('visio', CheckBoxType::class, [
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BatchCreate::class,
            'submit'     => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'spot_batch_create';
    }
}
