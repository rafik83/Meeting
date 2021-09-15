<?php


namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Happening;


use Proximum\Vimeet\Application\Command\Happening\EvaluateHappening;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\RatingType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvaluateHappeningType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('evaluation', RatingType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => EvaluateHappening::class,
            ])
        ;
    }
}
