<?php

/*
 * This file is part of the vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event;

use Proximum\Vimeet\Application\View\Tip\Event\TipTranslationView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TipSelectType extends AbstractType
{
    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /**
     * TipSelectType constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['tipViews']);
        $resolver->setAllowedTypes('tipViews', 'array');
        $resolver->setDefaults([
            'choice_label' => function (TipTranslationView $tipView) {
                return $tipView->adminTitle;
            },
            'choices' => function (Options $options) {
                return $options['tipViews'];
            },
            'choice_attr'  => function(TipTranslationView $tipTranslationView) {
                return [
                    'data-preview-url' => $this->urlGenerator->generate('admin_tip_event_preview', [
                        'tipTranslation' => $tipTranslationView->id,
                    ]),
                ];
            },
            'placeholder' => 'form.tip_event_affect.children.tip.placeholder'
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
