<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant;

use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Template\AbstractBlockType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractBlockType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (count($options['locales']) > 1) {
            $localeChoices = [];
            foreach ($options['locales'] as $locale) {
                $localeChoices[$locale] = Intl::getLocaleBundle()->getLocaleName($locale);
            }

            $builder->add('locale', ChoiceType::class, ['choices' => array_flip($localeChoices), 'mapped' => false]);
        }


        parent::buildForm($builder, $options);
    }

    protected function getObjects(array $options): array
    {
        /** @var Template\TemplateData $template */
        $template = $options['template'];

        return $template->getProfileObjects();
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class'        => Template\TemplateData::class,
            'validation_groups' => ['Default', 'profile'],
        ]);
        $resolver->setRequired(['template']);
        $resolver->setAllowedTypes('template', Template\TemplateData::class);
    }
}
