<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Application\Command\Meeting\UpdateMeetingRequest;
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
        $arrayAttributesOptions = array_merge(['required' => false], $options['priorityNumberAvailable'] === 0 ? ['disabled' => true] : []);

        if ($sheet->getType()->getPriorityMeetingRequestsNumber() > 0) {
            $builder
                ->add(
                    'isPriority', CheckboxType::class, $arrayAttributesOptions
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
