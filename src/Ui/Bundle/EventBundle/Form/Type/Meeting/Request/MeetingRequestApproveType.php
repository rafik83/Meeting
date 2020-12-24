<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Application\Command\Meeting\ApproveRequest;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingRequestApproveType extends AbstractMeetingRequestType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Sheet $sheet */
        $sheet = $options['sheet'];

        if ($sheet->getType()->getPriorityMeetingRequestsNumber() > 0) {
            $builder
                ->add(
                    'toPriority',
                    CheckboxType::class,
                    [
                        'required' => false,
                        'disabled' => $options['priorityNumberAvailable'] === 0
                    ]
                );
        }

        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['sheet', 'locale', 'priorityNumberAvailable']);
        $resolver->setAllowedTypes('sheet', Sheet::class);
        $resolver->setDefault('placeholder_description', 'form.catalog_approve_meeting_request.children.description.placeholder');
        $resolver->setDefault('show_description', true);
        $resolver->setDefaults([
            'data_class' => ApproveRequest::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'catalog_approve_meeting_request';
    }
}
