<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Catalog;

use Proximum\Vimeet\Domain\Model\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CatalogTagFilterTranslationLocaleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Event $event */
        $event = $options['event'];

        foreach ($event->getLocales() as $locale) {
            $builder->add($locale, CatalogTagFilterTranslationsType::class, [
                'label' => ucfirst(Intl::getLocaleBundle()->getLocaleName($locale)),
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'event' => null,
        ]);
    }
}
