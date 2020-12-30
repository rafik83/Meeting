<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package;

use Proximum\Vimeet\Application\Command\Package\Create;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\EventChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
            ])
        ;

        if (true === $options['selectEvent']) {
            $builder
                ->add('event', EventChoiceType::class, [
                    'required'         => true,
                    'repositoryMethod' => function (EventRepositoryInterface $eventRepository) use ($options) {
                        return $eventRepository->getEventsByAdmin($options['user']);
                    },
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['user']);
        $resolver->setDefaults([
            'data_class' => Create::class,
            'selectEvent' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'package_create';
    }
}
