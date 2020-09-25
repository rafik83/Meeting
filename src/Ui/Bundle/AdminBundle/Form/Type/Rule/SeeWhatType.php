<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Rule;

use Proximum\Vimeet\Domain\Model\Rule;
use Proximum\Vimeet\Domain\Rule\TagIdentifier;
use Proximum\Vimeet\Infrastructure\Adapter\TranslatorAdapter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeeWhatType extends AbstractType
{
    /**
     * @var TagIdentifier
     */
    private $tagIdentifier;

    /**
     * @var TranslatorAdapter
     */
    private $translator;

    /**
     * @param TagIdentifier     $tagIdentifier
     * @param TranslatorAdapter $translator
     */
    public function __construct(TagIdentifier $tagIdentifier, TranslatorAdapter $translator)
    {
        $this->tagIdentifier = $tagIdentifier;
        $this->translator    = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $locale = $options['locale'];

        $builder
            ->add('priority', IntegerType::class, [
                'required' => true,
                'attr'     => [
                    'min' => 1,
                ],
            ])
            ->add('seeWhat', ChoiceType::class, [
                'attr'         => [
                    'data-dual-list-box'                      => true,
                    'data-dual-list-box-selectedListLabel'    => $this->translator->trans(
                        'form.rule_see_what.see_what_column', [], 'forms', $locale
                    ),
                    'data-dual-list-box-nonSelectedListLabel' => $this->translator->trans(
                        'form.rule_see_what.not_see_what_column', [], 'forms', $locale
                    ),
                ],
                'choices'      => $this->tagIdentifier->identify($options['rule']->getSeeable()),
                'choice_label' => function ($value) {
                    return sprintf('%s%s', 'template.tag.', $value);
                },
                'choice_translation_domain' => 'templates',
                'multiple'                  => true,
                'required'                  => false,
            ])
        ;

        $grades = range(1, 5);
        $gradeChoices = array_combine($grades, $grades);
        $builder
            ->add('phoneAccessMinEvaluation', ChoiceType::class, [
                'required' => false,
                'placeholder' => 'form.rule_see_what.children.unrestricted',
                'choices' => $gradeChoices,
            ])
            ->add('emailAccessMinEvaluation', ChoiceType::class, [
                'required' => false,
                'placeholder' => 'form.rule_see_what.children.unrestricted',
                'choices' => $gradeChoices,
            ])
            ->add('requestAutomaticallyTransformedIntoMeeting', CheckboxType::class, [
                'required' => false,
            ])
        ;

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('locale')
            ->setRequired('rule')
            ->setAllowedTypes('rule', Rule::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'rule_see_what';
    }
}
