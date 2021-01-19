<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet;

use Proximum\Vimeet\Application\Command\Sheet\AddComment;
use Proximum\Vimeet\Domain\Sheet\CommercialStatus;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('reminderDate', DateTimePickerType::class, [
                'required' => false,
            ])
            ->add('commercialStatus', CommercialStatusChoiceType::class, [
                'required'     => false,
                'choice_label' => function ($key) {
                    return sprintf('form.sheet_comment.children.commercialStatus.status.%s', $key);
                },
                'translation_domain' => 'forms',
                'attr' => [
                    'class' => 'commercial_status_selector',
                    'data-associated-label' => json_encode(CommercialStatus::STATUS_WITH_LABEL),
                ],
            ])
            ->add('text', TextareaType::class, [
                'placeholder' => 'form.sheet_comment.children.text.placeholder',
                'required'    => false,
                'label'       => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => AddComment::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_comment';
    }
}
