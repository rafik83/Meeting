<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\Model;

use Proximum\Vimeet\Application\Command\Package\Model\Plans;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlansType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('enabled', CheckboxType::class, [
                'required' => false,
            ])
            ->add('labels', TranslationsType::class, [
                'entry_type' => TextType::class,
                'locales'    => $options['event']->getLocales(),
                'required'   => false,
            ])
            ->add('plans', ProductCollectionType::class, [
                'event'            => $options['event'],
                'locale'           => $options['locale'],
                'product_types'    => [Product::TYPE_PLAN],
                'collection_group' => 'plans',
                'error_bubbling'   => false,
                'required'         => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults([
            'data_class' => Plans::class,
        ]);
    }
}
