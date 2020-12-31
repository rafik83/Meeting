<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant;

use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Template\AbstractBlockType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractBlockType
{
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
