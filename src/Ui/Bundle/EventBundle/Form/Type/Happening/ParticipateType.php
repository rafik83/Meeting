<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2015 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Happening;

use Proximum\Vimeet\Application\Command\Happening\Participate;
use Proximum\Vimeet\Domain\Model\Happening;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipateType extends AbstractType
{
    const QUESTION_MAX_LENGTH = 300;

    /**
     * @var TranslatorAdapter
     */
    private $translator;

    /**
     * @param TranslatorAdapter $translator
     */
    public function __construct(TranslatorAdapter $translator)
    {
        $this->translator = $translator;
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

//        $builder
//            ->add('participants', ChoiceType::class, [
//                'choices'  => $options['participants'],
//                'multiple' => true,
//                'expanded' => true,
//            ]);
    }
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'happening',
//            'participants',
        ]);

        $resolver->setAllowedTypes('happening', Happening::class);

        $resolver->setDefaults([
            'data_class'    => Participate::class,
            'csrf_token_id' => 'participate_happening',
        ]);
    }
}
