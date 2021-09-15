<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Meeting\Visio;

use Proximum\Vimeet\Application\Command\Visio\UpdateVisioSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SettingsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('localizedVisioSettings', CollectionType::class, [
                'entry_type' => VisioSettingsLocalizedType::class,
                'label' => false,
                'required' => false,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options): void
    {
        foreach ($view->children['localizedVisioSettings'] as $translation) {
            $translation->vars['label'] = Intl::getLocaleBundle()->getLocaleName($translation->vars['name']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => UpdateVisioSettings::class,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'event_meeting_visio_settings';
    }
}
