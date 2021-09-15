<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Schedule;

use Proximum\Vimeet\Domain\Meeting\Slot\Recipe;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('begin', DateTimePickerType::class, ['view_timezone' => $options['event']->getTimeZone()])
            ->add('end', DateTimePickerType::class, ['view_timezone' => $options['event']->getTimeZone()])
            ->add('interval', IntegerType::class)
            ->add('duration', IntegerType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('event');
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => Recipe::class,
            'empty_data' => function (FormInterface $form) {
                return new Recipe(
                    $form->get('begin')->getData(),
                    $form->get('end')->getData(),
                    $form->get('interval')->getData(),
                    $form->get('duration')->getData()
                );
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'generate_slot_recipe';
    }
}
