<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Happening;

use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Domain\Model\Participant;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipateType extends AbstractType
{
    const QUESTION_MAX_LENGTH = 300;

    /** @var TranslatorAdapter */
    private $translator;

    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /**
     * @param TranslatorAdapter      $translator
     * @param ParticipantInfoGuesser $participantInfoGuesser
     */
    public function __construct(TranslatorAdapter $translator, ParticipantInfoGuesser $participantInfoGuesser)
    {
        $this->translator             = $translator;
        $this->participantInfoGuesser = $participantInfoGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Happening $happening */
        $happening = $options['happening'];

        if ($happening->isQuestionAllowed()) {
            $builder
                ->add('question', TextareaType::class, [
                    'required' => false,
                    'attr'     => [
                        'data-text-max-length-indicator'    => self::QUESTION_MAX_LENGTH,
                        'data-text-max-length-translations' => sprintf(
                            '%s|%s|%s',
                            $this->translator->trans(
                                'form.sheet_editable_text_data.data.maxLength.translations.plural',
                                [],
                                'forms'
                            ),
                            $this->translator->trans(
                                'form.sheet_editable_text_data.data.maxLength.translations.singular',
                                [],
                                'forms'
                            ),
                            $this->translator->trans(
                                'form.sheet_editable_text_data.data.maxLength.translations.reached',
                                [],
                                'forms'
                            )
                        ),
                    ],
                ]);
        }

        if (true === $options['isParticipantsEnabled']) {
            $builder
                ->add(
                    'participants',
                    ChoiceType::class,
                    [
                        'choices'  => $options['participants'],
                        'choice_label' => function (Participant $participant) use ($options) {
                            return $this->participantInfoGuesser
                                ->guessParticipantCompleteName($participant, $options['locale']);
                        },
                        'multiple' => true,
                        'expanded' => true,
                    ]
                );
        }

        if ($happening->isPrivate() && false === $options['isUpdate']) {
            $builder->add('invitationCode', TextType::class, [
                'required' => true,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'happening',
            'participants',
            'isParticipantsEnabled',
            'isUpdate',
            'locale',
        ]);

        $resolver->setAllowedTypes('happening', Happening::class);
        $resolver->setAllowedTypes('isParticipantsEnabled', 'boolean');

        $resolver->setDefaults([
            'data_class'    => Participate::class,
            'csrf_token_id' => 'participate_happening',
        ]);
    }
}
