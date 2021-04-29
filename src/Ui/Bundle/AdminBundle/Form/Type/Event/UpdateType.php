<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Event\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Repository\Invoice\PrefixRepositoryInterface;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Transformer\UniqueValuesTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UpdateType extends AbstractEventType
{
    /** @var TranslatorInterface */
    private $translator;

    /** @var Event\EventUrlGeneratorInterface */
    private $eventUrlGenerator;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(
        array $supportedCurrencies,
        array $preferredLocales,
        PrefixRepositoryInterface $prefixRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        TranslatorInterface $translator,
        Event\EventUrlGeneratorInterface $eventUrlGenerator,
        UniqueValuesTransformer $uniqueValuesTransformer
    ) {
        $this->eventUrlGenerator = $eventUrlGenerator;
        $this->translator = $translator;
        $this->authorizationChecker = $authorizationChecker;
        parent::__construct($supportedCurrencies, $preferredLocales, $prefixRepository, $authorizationChecker, $uniqueValuesTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        /** @var Event $event */
        $event = $options['event'];

        $builder
            ->add('translations', CollectionType::class, [
                'entry_type' => UpdateTranslationType::class,
                'label'      => false,
            ])
            ->add('analyticsCode', TextType::class, [
                'required' => false,
            ])
            ->add('displayParticipantNameOnPlanning', CheckboxType::class, [
                'required' => false,
            ])
            ->add('displayParticipantPositionOnPlanning', CheckboxType::class, [
                'required' => false,
            ])
            ->add('googleLoginEnabled', CheckboxType::class, [
                'required' => false,
                'help' => $this->translator->trans(
                    'form.event_update.children.googleLoginEnabled.help',
                    ['%urls%' => implode(" ; ", $this->getLocalesUrl($event, 'connect_google_check'))],
                    'forms'
                )
            ])
            ->add('linkedinLoginEnabled', CheckboxType::class, [
                'required' => false,
                'help' => $this->translator->trans(
                    'form.event_update.children.linkedinLoginEnabled.help',
                    ['%urls%' => implode(" ; ", $this->getLocalesUrl($event, 'connect_linkedin_check'))],
                    'forms'
                )
            ]);

        $isSuperAdmin = $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN');

        if ($isSuperAdmin) {
            $builder
                ->add(
                    'accessControlEnabled',
                    CheckboxType::class,
                    [
                        'required' => false,
                    ]
                )
                ->add(
                    'showCheckinStatus',
                    CheckboxType::class,
                    [
                        'required' => false,
                    ]
                )
                ->add('apiKeyAvailable', CheckboxType::class, [
                    'required' => false,
                    'help' => $options['api_key']
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children['translations'] as $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($translation->vars['name']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(['locales', 'event']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => Update::class,
            'api_key' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'event_update';
    }

    private function getLocalesUrl(Event $event, string $routeName)
    {
        $urls = [];

        foreach ($event->getLocales() as $locale) {
            $urls[$locale] = $this->eventUrlGenerator->generateEventAbsoluteUrl($event, $routeName, ['_locale' => $locale]);
        }

        return $urls;
    }
}
