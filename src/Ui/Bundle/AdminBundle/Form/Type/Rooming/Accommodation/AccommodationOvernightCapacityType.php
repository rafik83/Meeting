<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rooming\Accommodation;

use Proximum\Vimeet\Application\Command\Rooming\Accommodation\AccommodationOvernightCapacityView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccommodationOvernightCapacityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $year = (int) $options['firstDay']->format('Y');

        $builder
            ->add('date', DateType::class, [
                'years' => range($year -1, $year + 1),
            ])
            ->add('capacity', IntegerType::class, [
                'attr' => [
                    'min' => 0,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['firstDay'])
            ->setDefaults([
                'data_class' => AccommodationOvernightCapacityView::class
            ])
        ;
    }
}
