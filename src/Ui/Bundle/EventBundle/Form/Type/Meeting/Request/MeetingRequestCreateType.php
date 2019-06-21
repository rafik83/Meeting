<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Application\Command\Meeting\CreateRequest;
use Proximum\Vimeet\Domain\Model\Sheet;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingRequestCreateType extends AbstractMeetingRequestType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Sheet $sheet */
        $sheet = $options['sheet'];

        if ($sheet->getType()->getPriorityMeetingRequestsNumber() > 0) {
            $builder
                ->add(
                    'fromPriority', CheckboxType::class, [
                        'required' => false,
                    ]
                );
        }

        if ($options['priorityNumberAvailable'] === 0) {
            $builder
                ->remove('fromPriority')
                ->add(
                    'fromPriority', CheckboxType::class, [
                        'disabled' => true,
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

        $resolver->setDefault('placeholder_description', 'form.catalog_create_meeting_request.children.description.placeholder');
        $resolver->setDefaults([
            'data_class' => CreateRequest::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'catalog_create_meeting_request';
    }
}
