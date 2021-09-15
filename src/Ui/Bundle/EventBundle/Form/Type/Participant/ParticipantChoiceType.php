<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant;

use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantChoiceType extends AbstractType
{
    /**
     * @var ParticipantInfoGuesser
     */
    private $participantInfoGuesser;

    /**
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
            'isMultiple' => false,
            'isSelect2' => false,
            'choice_label' => function (Options $options) {
                return function (Participant $participant) use ($options) {
                    return $this->participantInfoGuesser
                        ->guessParticipantCompleteName($participant, $options['locale']);
                };
            },
            'choices'      => function (Options $options) {
                return $options['sheet']->getParticipants();
            },
            'multiple' => function (Options $options) {
                return $options['isMultiple'] ?? false;
            },
        ]);
        $resolver->setRequired(['sheet', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
