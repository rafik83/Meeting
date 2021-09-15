<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\Registration;

use Proximum\Vimeet\Application\Command\Template\Registration\Create;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => Create::class,
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'registration_template_create';
    }
}
