<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Operator;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\EventChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('event', EventChoiceType::class, [
                'label' => false,
                'required' => false,
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'form.filter_operator.event.all',
                'choices' => $options['events'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'form.filter_operator.children.submit.label',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'events',
        ]);
        $resolver->setDefaults([
            'required' => false,
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}
