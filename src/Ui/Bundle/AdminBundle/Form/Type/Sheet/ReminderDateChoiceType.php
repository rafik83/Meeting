<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReminderDateChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('begin', DateTimePickerType::class, [
                'label' => 'form.sheet.reminderDate.children.begin.label',
                'display_hour' => false,
                'format' => 'd/m/Y',
                'locale' => $options['locale'],
                'required' => false,
            ])
            ->add('end', DateTimePickerType::class, [
                'label' => 'form.sheet.reminderDate.children.end.label',
                'display_hour' => false,
                'format' => 'd/m/Y',
                'locale' => $options['locale'],
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'locale' => 'fr',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'sheet_reminder_date_choice';
    }
}
