<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Invoice\Prefix;
use Proximum\Vimeet\Domain\Repository\Invoice\PrefixRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Transformer\UniqueValuesTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
    private array $supportedCurrencies;
    private PrefixRepositoryInterface $prefixRepository;
    private AuthorizationCheckerInterface $authorizationChecker;
    private array $preferredLocales;
    private UniqueValuesTransformer $uniqueValuesTransformer;

    public function __construct(
        array $supportedCurrencies,
        array $preferredLocales,
        PrefixRepositoryInterface $prefixRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        UniqueValuesTransformer $uniqueValuesTransformer
    ) {
        $this->supportedCurrencies = $supportedCurrencies;
        $this->prefixRepository = $prefixRepository;
        $this->authorizationChecker = $authorizationChecker;
        $this->preferredLocales = $preferredLocales;
        $this->uniqueValuesTransformer = $uniqueValuesTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currentLocale = $options['currentLocale'];

        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add('title', TextType::class)
            ->add('visible', CheckboxType::class, [
                'required' => false,
            ])
            ->add('locales', LocaleType::class, [
                'multiple'          => true,
                'preferred_choices' => $this->preferredLocales,
            ]);
        $builder->get('locales')->addModelTransformer($this->uniqueValuesTransformer);

        $builder
            ->add('domain', TextType::class, [
                'placeholder' => 'form.event_create.children.domain.placeholder',
                'help' => 'form.event_create.children.domain.help',
            ])
            ->add('timeZone', TimezoneType::class)
            ->add('fallback', LocaleType::class, [
                'preferred_choices' => $this->preferredLocales,
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
            ->add('organiserName', TextType::class, [
                'required' => true,
            ])
            ->add('emailTeam', EmailType::class, [
                'required' => false,
            ])
        ;

        // default invoicePrefix choice type options
        $invoicePrefixOptions = [
            'required'     => true,
            'expanded'     => false,
            'multiple'     => false,
        ];

        $isSuperAdmin = $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');

        // Show when admin OR when create new Event
        if (true === $isSuperAdmin || $this instanceof CreateType) {
            $preferredChoices = [];

            if (!$isSuperAdmin) {
                $defaultPrefix = $this->prefixRepository->getDefault();
                $prefixes = null !== $defaultPrefix ? [$defaultPrefix] : [];
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
                'choices'  => null !== $invoicePrefix ? [$invoicePrefix] : [],
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

        $builder
            ->add('welcomeEnabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('visio', CheckboxType::class, [
                'required' => false,
            ])
            ->add('disabledEmailChanging', CheckboxType::class, [
                'required' => false,
            ])
            ->add('disabledPasswordChanging', CheckboxType::class, [
                'required' => false,
            ])
            ->add('autoArchiveWebinar', CheckboxType::class, [
                'required' => false,
            ])
        ;
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
