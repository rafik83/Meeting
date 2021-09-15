<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\EventRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventChoiceType extends AbstractType
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined('event');
        $resolver->setDefined('removeCurrentEvent');
        $resolver->setDefaults([
            'class'            => Event::class,
            'choice_label'     => 'title',
            'repositoryMethod' => function (EventRepositoryInterface $eventRepository) {
                return $eventRepository->getAll();
            },
            'choices'          => function (Options $options) {
                $events = $options['repositoryMethod']($this->eventRepository);

                if (isset($options['removeCurrentEvent'])
                    && true === $options['removeCurrentEvent']
                    && isset($options['event'])
                ) {
                    foreach ($events as $key => $event) {
                        if ($event === $options['event']) {
                            unset($events[$key]);
                            break;
                        }
                    }
                }

                return $events;
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
