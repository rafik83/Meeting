<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Application\Command\Event\ArchiveUnArchive;
use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArchiveUnArchiveType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Event $event */
        $event = $options['event'];

        if ($event->isArchived()) {
            $builder->add('unArchive', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg',
                ],
            ]);
        } else {
            $builder->add('archive', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-lg',
                ],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('event');
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => ArchiveUnArchive::class,
        ]);
    }
}
