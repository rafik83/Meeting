<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Application\Command\Meeting\RefuseRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingRequestRefuseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', TextType::class, [
                'placeholder' => 'form.catalog_refuse_meeting_request.children.message.placeholder',
                'required'    => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RefuseRequest::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'catalog_refuse_meeting_request';
    }
}
