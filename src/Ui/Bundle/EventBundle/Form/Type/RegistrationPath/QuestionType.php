<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\RegistrationPath;

use Proximum\Vimeet\Application\Query\RegistrationPath\QuestionView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuestionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var QuestionView $questionView */
        $questionView = $options['questionView'];

        $builder
            ->add('answer', ChoiceType::class, [
                'label' => false,
                'choices' => $questionView->answerViews,
                'expanded' => true,
                'required' => true,
                'choice_label' => 'title',
                'choice_value' => 'id',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['questionView']);
        $resolver->setAllowedTypes('questionView', QuestionView::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'registration_path_question_type';
    }
}
