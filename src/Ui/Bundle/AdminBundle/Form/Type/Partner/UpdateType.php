<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Partner;

use Proximum\Vimeet\Application\Command\Partner\Update;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends PartnerType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
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
            ->add('types', TypeChoiceType::class, [
                'multiple' => true,
                'choices'  => $this->buildChoices($options['events'], $options['locale']),
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
            'data_class' => Update::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'update_partner';
    }
}
