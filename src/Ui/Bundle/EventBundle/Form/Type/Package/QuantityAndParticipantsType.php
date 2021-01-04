<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Package;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Package\Step\OptionRow;
use Proximum\Vimeet\Domain\Model\Sheet;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant\ParticipantChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuantityAndParticipantsType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isAttributable = (bool) $options['isAttributable'];

        if (!$isAttributable) {
            $builder
                ->add(
                    'quantity',
                    QuantityType::class,
                    [
                        'max' => $options['max'],
                        'minMessage' => $options['minMessage'],
                        'maxMessage' => $options['maxMessage'],
                    ]
                );
        } else {
            $builder
                ->add(
                    'participants',
                    ParticipantChoiceType::class,
                    [
                        'label' => $this->translator->transChoice(
                            'form.options.selectParticipants.label',
                            $options['max'],
                            [],
                            'forms'
                        ),
                        'help' => $this->getParticipantsChoiceHelp(
                            $options['max'],
                            $options['sheet']->countParticipants()
                        ),
                        'sheet' => $options['sheet'],
                        'locale' => $options['locale'],
                        'isMultiple' => true,
                        'required' => false,
                        'attr' => [
                            'data-max' => $options['max'],
                            'data-min-message' => $options['minMessage'],
                            'data-max-message' => $options['maxMessage'],
                        ],
                    ]
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver->setRequired(
            [
                'sheet',
                'locale',
                'max',
                'minMessage',
                'maxMessage',
                'isAttributable',
            ]
        );
        $optionsResolver->addAllowedTypes('sheet', Sheet::class);
        $optionsResolver->addAllowedTypes('isAttributable', 'boolean');
        $optionsResolver->setDefaults(
            [
                'data_class' => OptionRow::class,
            ]
        );
    }

    private function getParticipantsChoiceHelp($max, int $participantsNumber)
    {
        if ($max >= $participantsNumber) {
            return false;
        }

        return $this->translator->transChoice(
            'form.options.selectParticipantsQuantityMax.label',
            $max,
            ['%max%' => $max],
            'forms'
        );
    }
}
