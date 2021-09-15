<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant;

use Doctrine\ORM\EntityRepository;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantEntityType extends AbstractType
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
     * ParticipantEntityType constructor.
     *
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'class'         => Participant::class,
            'query_builder' => function (Options $options) {
                return function (EntityRepository $entityRepository) use ($options) {
                    return $entityRepository
                        ->createQueryBuilder('participant')
                        ->where('participant.sheet = :sheet')
                        ->setParameter('sheet', $options['sheet']);
                };
            },
            'choice_label' => function (Options $options) {
                return function (Participant $participant) use ($options) {
                    return $this->participantInfoGuesser
                        ->guessParticipantCompleteName($participant, $options['locale']);
                };
            },
        ]);

        $resolver->setRequired(['sheet', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntityType::class;
    }
}
