<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) 2016 Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Repository\Invoice\PrefixRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class AbstractEventType extends AbstractType
{
    /**
     * @var array
     */
    private $supportedCurrencies;

    /**
     * @var PrefixRepositoryInterface
     */
    private $prefixRepository;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @param array                         $supportedCurrencies
     * @param PrefixRepositoryInterface     $prefixRepository
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(
        array $supportedCurrencies,
        PrefixRepositoryInterface $prefixRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->supportedCurrencies  = $supportedCurrencies;
        $this->prefixRepository     = $prefixRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $prefered      = ['fr', 'en', 'es', 'de', 'it', 'zh'];
        $currentLocale = $options['currentLocale'];

        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add('title', TextType::class)
            ->add('locales', LocaleType::class, [
                'multiple'          => true,
                'preferred_choices' => $prefered,
            ])
            ->add('domain', TextType::class, [
                'placeholder' => 'form.event_create.children.domain.placeholder',
            ])
            ->add('timeZone', TimezoneType::class)
            ->add('fallback', LocaleType::class, [
                'preferred_choices' => $prefered,
            ])
            ->add('logo', FileType::class, [
                'required' => false,
                'attr'     => [
                    'accept' => implode(', ', [
                        "image/jpeg",
                        "image/pjpeg",
                        "image/png",
                        "image/x-png",
                        'image/svg+xml',
                    ]),
                ],
            ])
            ->add('country', CountryType::class)
            ->add('mode', VatModeType::class, [
                'expanded' => true,
            ])
            ->add('vat', NumberType::class)
            ->add('currency', ChoiceType::class, [
                'placeholder'  => 'form.event_update.children.currency.placeholder',
                'required'     => true,
                'choices'      => $this->supportedCurrencies,
                'choice_label' => function ($currentChoice) use ($currentLocale) {
                    return Intl::getCurrencyBundle()->getCurrencyName($currentChoice, $currentLocale);
                },
            ])
            ->add('leftColor', TextType::class)
            ->add('rightColor', TextType::class)
            ->add('textColor', TextType::class)
            ->add('organiserName', TextType::class, [
                'required' => true,
            ])
            ->add('emailTeam', EmailType::class, [
                'required' => false,
            ]);

        // default invoicePrefix choice type options
        $invoicePrefixOptions = [
            'required'     => true,
            'expanded'     => false,
            'multiple'     => false,
        ];

        $isSuperAdmin = $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');

        // Show when admin OR when create new Event
        if ($isSuperAdmin === true || $this instanceof CreateType) {
            $preferredChoices = [];

            if (!$isSuperAdmin) {
                $defaultPrefix = $this->prefixRepository->getDefault();
                $prefixes = $defaultPrefix !== null ? [$defaultPrefix] : [];
                $invoicePrefixOptions['help'] = 'form.event.children.invoicePrefix.help.label';
            } else {
                $prefixes = $this->prefixRepository->getAll();

                foreach ($prefixes as $prefix) {
                    if ($prefix->isDefault()) {
                        $preferredChoices = [$prefix];
                        break;
                    }
                }
            }

            $invoicePrefixOptions = array_merge($invoicePrefixOptions, [
                'choices'           => $prefixes,
                'preferred_choices' => $preferredChoices,
            ]);
        } elseif ($event instanceof Event) {
            $invoicePrefix = $event->getInvoicePrefix();

            $invoicePrefixOptions = array_merge($invoicePrefixOptions, [
                'disabled' => true,
                'choices'  => $invoicePrefix !== null ? [$invoicePrefix] : [],
                'help'     => 'form.event.children.invoicePrefix.help.label',
            ]);
        }

        $invoicePrefixOptions['choice_label'] = function ($prefix = null) {
            if ($prefix instanceof Prefix) {
                return $prefix->getPrefixExample();
            }

            return null;
        };

        $builder->add('invoicePrefix', ChoiceType::class, $invoicePrefixOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['event' => null]);
        $resolver->setRequired(['currentLocale']);
    }
}
