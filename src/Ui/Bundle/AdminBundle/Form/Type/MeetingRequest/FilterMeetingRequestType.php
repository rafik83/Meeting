<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\MeetingRequest;

use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Repository\Meeting\RequestRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterMeetingRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('state', ChoiceType::class, [
                'label'                     => 'form.meeting_request_filter.children.state.label',
                'choices'                   => [
                    'admin.meeting_request.state.approved' => Request::STATE_APPROVED,
                    'admin.meeting_request.state.refused'  => Request::STATE_REFUSED,
                    'admin.meeting_request.state.sent'     => Request::STATE_SENT,
                    'admin.meeting_request.state.planned'  => Request::STATE_PLANNED,
                ],
                'placeholder'               => '',
                'choice_translation_domain' => 'messages',
            ])
            ->add('orderBy', ChoiceType::class, [
                'choice_translation_domain' => 'forms',
                'label'                     => 'form.meeting_request_filter.children.order_by.label',
                'choices'                   => [
                    'meeting_request_filter.order_by.options.created_at_asc'        => RequestRepositoryInterface::ORDER_BY_CREATE_AT_ASC,
                    'meeting_request_filter.order_by.options.created_at_desc'       => RequestRepositoryInterface::ORDER_BY_CREATE_AT_DESC,
                    'meeting_request_filter.order_by.options.state_updated_at_asc'  => RequestRepositoryInterface::ORDER_BY_STATE_UPDATED_AT_ASC,
                    'meeting_request_filter.order_by.options.state_updated_at_desc' => RequestRepositoryInterface::ORDER_BY_STATE_UPDATED_AT_DESC,
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'required'           => false,
            'method'             => 'GET',
            'csrf_protection'    => false,
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'meeting_request_filter';
    }
}
