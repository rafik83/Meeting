<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot;

use Proximum\Vimeet\Application\Command\Spot\Create;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpotCreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reference', TextType::class, [
                'required' => true,
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
            ->add('visio', CheckboxType::class, [
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
            'data_class'    => Create::class,
            'csrf_token_id' => 'spot_create',
        ]);
    }
}
