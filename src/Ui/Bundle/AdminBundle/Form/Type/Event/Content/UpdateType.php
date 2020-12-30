<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Event\Content;

use Proximum\Vimeet\Application\Command\Event\Content\Update;
use Proximum\Vimeet\Domain\Model\Event\Content;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('translations', TranslationsType::class, [
                'label'         => false,
                'locales'       => $options['content']->getEvent()->getLocales(),
                'entry_type'    => ContentTranslationType::class,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['content']);
        $resolver->setAllowedTypes('content', Content::class);
        $resolver->setDefaults([
            'data_class' => Update::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'content_update';
    }
}
