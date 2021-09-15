<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode;

use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PromotionCodeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class)
            ->add('code', TextType::class)
            ->add('validUntil', DateTimePickerType::class, [
                'required'      => false,
                'view_timezone' => $options['event']->getTimeZone(),
            ])
            ->add('stock', IntegerType::class, ['required' => false])
            ->add('translations', TranslationsType::class, [
                'locales'       => $options['event']->getLocales(),
                'entry_type'    => TranslationType::class,
                'entry_options' => [],
                'label'         => false,
            ])
            ->add('promotions', CollectionType::class, [
                'entry_type'     => PromotionType::class,
                'entry_options'  => [
                    'event'          => $options['event'],
                    'label'          => false,
                    'error_bubbling' => false,
                    'locale'         => $options['locale'],
                ],
                'allow_add'      => true,
                'allow_delete'   => true,
                'error_bubbling' => false,
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
    }
}
