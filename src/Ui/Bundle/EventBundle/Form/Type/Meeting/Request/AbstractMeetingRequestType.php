<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Meeting\Request;

use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Domain\Template\ParticipantInfoGuesser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractMeetingRequestType extends AbstractType
{
    /** @var ParticipantInfoGuesser */
    private $participantInfoGuesser;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        ParticipantInfoGuesser $participantInfoGuesser,
        TranslatorInterface $translator
    ) {
        $this->participantInfoGuesser = $participantInfoGuesser;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Sheet $sheet */
        $sheet = $options['sheet'];
        $locale = $options['locale'];

        if ($options['show_description']) {
            $builder
                ->add('description', TextareaType::class, [
                    'placeholder' => $options['placeholder_description'],
                    'required'    => false,
                    'attr' => [
                        'data-text-max-length-indicator' => 300,
                        'data-text-max-length-translations' => sprintf(
                        '%s|%s|%s',
                        $this->translator->trans('form.sheet_editable_text_data.data.maxLength.translations.plural', [], 'forms', $locale),
                        $this->translator->trans('form.sheet_editable_text_data.data.maxLength.translations.singular', [], 'forms', $locale),
                        $this->translator->trans('form.sheet_editable_text_data.data.maxLength.translations.reached', [], 'forms', $locale)
                    )
                    ]
                ])
            ;
        }

        if (!$sheet->getType()->areAllSheetParticipantsAssignedToMeeting() && 1 < $sheet->countParticipants()) {
            $builder
                ->add('participants', ChoiceType::class, [
                    'choices'      => $sheet->getParticipants()->toArray(),
                    'choice_label' => function ($participant) use ($options) {
                        return $this->participantInfoGuesser
                            ->guessParticipantCompleteName($participant, $options['locale']);
                    },
                    'expanded'     => true,
                    'multiple'     => true,
                    'required'     => false,
                    'choice_attr'  => function () {
                        return ['class' => 'request-checkbox-select-participant'];
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
        $resolver->setRequired(['sheet', 'locale', 'priorityNumberAvailable']);
        $resolver->setDefault('placeholder_description', '');
        $resolver->setDefault('show_description', true);
    }
}
