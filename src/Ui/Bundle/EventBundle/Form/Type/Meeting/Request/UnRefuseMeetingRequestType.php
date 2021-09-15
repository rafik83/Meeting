<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Application\Command\Meeting\UnRefuseMeetingRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnRefuseMeetingRequestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UnRefuseMeetingRequest::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'catalog_un_refuse_meeting_request';
    }
}
