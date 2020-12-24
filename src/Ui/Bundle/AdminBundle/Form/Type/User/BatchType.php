<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User;

use Proximum\Vimeet\Application\Command\User\Batch\Batch;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Template\FormTemplateChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ids', ChoiceType::class, [
                'choices' => $options['ids'],
                'choice_name' => function ($id) {
                    return $id;
                },
                'expanded' => true,
                'multiple' => true,
                'label' => false,
                'translation_domain' => false,
            ])
            ->add('campaignTitle', TextType::class, [
                'required' => false,
            ])
            ->add('sendMail', SubmitType::class)
            ->add('formTemplate', FormTemplateChoiceType::class, [
                'event' => $options['event'],
                'required' => false,
                'placeholder' => false,
            ])
            ->add('exportFormTemplate', SubmitType::class)
            ->add('selectionType', HiddenType::class, [
                'data' => Batch::SELECTION_TYPE_PAGE,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['ids', 'event'])
            ->setAllowedTypes('ids', ['array'])
            ->setAllowedTypes('event', Event::class)
            ->setDefaults(['data_class' => Batch::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'user_batch';
    }
}
