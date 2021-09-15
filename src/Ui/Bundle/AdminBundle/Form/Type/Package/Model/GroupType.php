<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Package\Model;

use Proximum\Vimeet\Application\Command\Package\Model\Group;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('labels', TranslationsType::class, [
                'entry_type'     => TextType::class,
                'locales'        => $options['event']->getLocales(),
                'required'       => false,
                'error_bubbling' => false,
            ])
            ->add('options', ProductCollectionType::class, [
                'event'            => $options['event'],
                'locale'           => $options['locale'],
                'product_types'    => [Product::TYPE_OPTION],
                'collection_group' => 'options',
                'error_bubbling'   => false,
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
            'data_class' => Group::class,
        ]);
    }
}
