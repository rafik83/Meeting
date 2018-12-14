<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Accommodation;

use Proximum\Vimeet\Application\Command\Rooming\Accommodation\Add;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [])
            ->add('overnightCapacities', CollectionType::class, [
                'entry_type' => AccommodationOvernightCapacityType::class,
                'allow_add' => true,
                'allow_delete' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Add::class
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'admin_bundle_add_type';
    }
}
