<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\MeetingRequest;

use Proximum\Vimeet\Application\Command\MeetingRequest\Admin\LockMeetingRequestUpdate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LockMeetingRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lock', ChoiceType::class, [
                'choices'  => [
                    'form.meeting_request_update_lock.choice.yes' => true,
                    'form.meeting_request_update_lock.choice.no'  => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LockMeetingRequestUpdate::class,
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'meeting_request_update_lock';
    }
}
