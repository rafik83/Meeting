<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Participant;

use Proximum\Vimeet\Application\Components\Sheet\Template\Tag;
use Proximum\Vimeet\Domain\Template;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data\ImageDataType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AvatarType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Template\TemplateData $template */
        $template = $options['template'];
        $key      = $options['key'];
        $locale   = $options['locale'];
        /** @var Template\TemplateObject\Image $avatar */
        $avatar   = $template->getObject($key);

        $builder->add($key, ImageDataType::class, [
            'locale'    => $locale,
            'label'     => $avatar->getOption('label', $locale),
            'showLabel' => true,
            'object'    => $avatar,
            'attr'      => [
                'image-preview' => $avatar->hasTag(Tag::PARTICIPANT_AVATAR),
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Template\TemplateData::class,
        ]);
        $resolver->setRequired(['template', 'locale', 'key']);
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setAllowedTypes('key', 'string');
        $resolver->setAllowedTypes('template', Template\TemplateData::class);
    }
}
