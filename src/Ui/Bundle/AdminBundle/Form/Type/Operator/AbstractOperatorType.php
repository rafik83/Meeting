<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Operator;

use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\EventChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AbstractOperatorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'required' => true,
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
            ])
            ->add('firstname', TextType::class, [
                'required' => true,
            ])
            ->add('events', EventChoiceType::class, [
                'required' => false,
                'expanded' => true,
                'multiple' => true,
                'placeholder' => '',
                'choices' => $options['events'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('events');
    }
}
