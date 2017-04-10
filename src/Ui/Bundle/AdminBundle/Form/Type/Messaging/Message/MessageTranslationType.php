<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Message;

use Proximum\Vimeet\Ui\Helper\Messaging\MessagePlaceholderHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MessageTranslationType extends AbstractType
{
    /** @var array */
    private $preferredLocales;

    /** @var MessagePlaceholderHelper */
    private $placeholderHelper;

    /**
     * MessageTranslationType constructor.
     *
     * @param array                    $preferredLocales
     * @param MessagePlaceholderHelper $placeholderHelper
     */
    public function __construct(array $preferredLocales, MessagePlaceholderHelper $placeholderHelper)
    {
        $this->preferredLocales = $preferredLocales;
        $this->placeholderHelper = $placeholderHelper;
    }

    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', LocaleType::class, ['preferred_choices' => $this->preferredLocales])
            ->add('subject', TextType::class)
            ->add('content', TextareaType::class, [
                'attr' => [
                    'data-placeholders' => json_encode($this->placeholderHelper->getPlaceholderData()),
                ],
            ]);
    }
}
