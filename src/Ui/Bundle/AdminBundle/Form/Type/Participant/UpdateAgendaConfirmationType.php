<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Participant;

use Proximum\Vimeet\Application\Command\User\Event\Token\UpdateAgendaConfirmation;
use Proximum\Vimeet\Domain\User\Event\AgendaConfirmation\Constant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateAgendaConfirmationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, [
                'expanded'                  => true,
                'choices'                   => Constant::AGENDA_CONFIRMATION_STATUS,
                'choice_label' => function ($value) {
                    return 'form.participant_update_agenda_confirmation_status.children.status.choice.' . $value;
                },
                'multiple'                  => false,
                'required'                  => true,
                'choice_translation_domain' => 'forms',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UpdateAgendaConfirmation::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'participant_update_agenda_confirmation_status';
    }
}
