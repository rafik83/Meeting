<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminEventChoiceType extends EventChoiceType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['admin']);
        $resolver->setAllowedTypes('admin', Admin::class);
        $resolver->setDefaults([
            'repositoryMethod' => function (Options $options) {
                return function (EventRepositoryInterface $eventRepository) use ($options) {
                    return $eventRepository->getEventsByAdmin($options['admin']);
                };
            },
       ]);
    }
}
