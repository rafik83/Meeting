<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Tip\Event;

use Proximum\Vimeet\Application\Command\Tip\Event\Affect;
use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AffectType extends AbstractType
{
    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tip', TipSelectType::class, [
                'locale' => $options['locale'],
                'attr'   => ['data-preview-tip' => true],
            ])
            ->add('types', TypeChoiceType::class, [
                'event'    => $options['event'],
                'expanded' => true,
                'locale'   => $options['locale'],
                'multiple' => true,
                'required' => false,
                'user'    => $options['admin'],
            ])
        ;
    }

    /**{@inheritdoc} */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([
            'admin',
            'event',
            'locale',
        ]);
        $resolver->setAllowedTypes('admin', Admin::class);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('locale', 'string');
        $resolver->setDefaults([
            'data_class' => Affect::class,
        ]);
    }

    /** {@inheritdoc} */
    public function getBlockPrefix()
    {
        return 'tip_event_affect';
    }
}
