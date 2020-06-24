<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Partner;

use Proximum\Vimeet\Application\Command\Partner\Create;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends PartnerType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = $this->buildChoices($options['events'], $options['locale']);

        $builder
            ->add('email', EmailType::class, [
                'required' => true,
            ])
            ->add('password', TextType::class, [
                'required' => isset($options['password_required']) ? $options['password_required'] : true,
            ])
            ->add('lastname', TextType::class, [
                'required' => true,
            ])
            ->add('firstname', TextType::class, [
                'required' => true,
            ])
            ->add('types', TypeChoiceType::class, [
                'multiple' => true,
                'choices'  => $choices,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'events',
            'locale',
        ]);
        $resolver->setDefaults([
            'data_class' => Create::class,
            'password_required' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'create_partner';
    }
}
