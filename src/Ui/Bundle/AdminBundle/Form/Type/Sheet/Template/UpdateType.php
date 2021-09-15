<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template;

use Proximum\Vimeet\Application\Command\Sheet\Template\Update;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', TextType::class);

        if (!$options['template']->getEvent()) {
            $locales = array_filter(array_flip(Intl::getLocaleBundle()->getLocaleNames()), function ($locale) use ($options) {
                return in_array($locale, $options['template']->getEnabledLocales());
            });

            $builder->add('fallback', LocaleType::class, ['choices' => $locales]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['template']);
        $resolver->setDefaults([
            'data_class' => Update::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_template_update';
    }
}
