<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Product\Import;

use Proximum\Vimeet\Application\Command\Product\Import\Import;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\EventChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImportType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('event', EventChoiceType::class, [
                'event'              => $options['event'],
                'expanded'           => false,
                'multiple'           => false,
                'repositoryMethod'   => function (EventRepositoryInterface $eventRepository) use ($options) {
                    return $eventRepository->getEventsByAdmin($options['admin']);
                },
                'required'           => true,
                'removeCurrentEvent' => $options['removeCurrentEvent'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('event');
        $resolver->setRequired('admin');
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('admin', Admin::class);
        $resolver->setDefaults([
            'data_class'         => Import::class,
            'removeCurrentEvent' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'import_products_and_template';
    }
}
