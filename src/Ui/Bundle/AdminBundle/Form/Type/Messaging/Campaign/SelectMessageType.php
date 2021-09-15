<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign;

use Proximum\Vimeet\Application\Command\Messaging\Campaign\SelectMessage;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SelectMessageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', MessageEntityType::class, [
                'event'    => $options['event'],
                'expanded' => true,
                'multiple' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
           'data_class' => SelectMessage::class,
        ]);
    }
}
