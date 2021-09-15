<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Application\Command\Meeting\UpdateMeetingRequest;
use Proximum\Vimeet\Domain\Model\Meeting\Request;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingRequestUpdateType extends AbstractMeetingRequestType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Sheet $sheet */
        $sheet = $options['sheet'];

        if ($sheet->getType()->getPriorityMeetingRequestsNumber() > 0) {
            /** @var Request $request */
            $request = $options['meetingRequest'];
            $isPriority = ($request->isFromPriority() && $sheet === $request->getFromSheet())
                || ($request->isToPriority() && $sheet === $request->getToSheet());

            $isDisabled = $options['priorityNumberAvailable'] === 0 && $isPriority === false;

            $builder
                ->add(
                    'isPriority',
                    CheckboxType::class,
                    [
                        'required' => false,
                        'disabled' => $isDisabled,
                        'data' => $isPriority
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
        parent::configureOptions($resolver);
        $resolver->setRequired(['meetingRequest']);
        $resolver->setDefault('placeholder_description', 'form.catalog_edit_meeting_request.children.description.placeholder');
        $resolver->setDefaults([
           'data_class' => UpdateMeetingRequest::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'catalog_edit_meeting_request';
    }
}
