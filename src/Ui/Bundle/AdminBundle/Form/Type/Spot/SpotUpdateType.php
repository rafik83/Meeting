<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Spot;

use Proximum\Vimeet\Application\Command\Spot\Update;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpotUpdateType extends AbstractType
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
            ->add('size', IntegerType::class, [
                'required' => true,
            ])
            ->add('meetingCapacity', IntegerType::class, [
                'required' => true,
            ])
            ->add('seatCapacity', IntegerType::class, [
                'required' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
           'data_class'    => Update::class,
           'csrf_token_id' => 'spot_create',
        ]);
    }
}
