<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\Batch;

use Proximum\Vimeet\Application\Command\PromotionCode\Batch\Create;
use Proximum\Vimeet\Application\Command\PromotionCode\Batch\Update;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Form\Type\DateTimePickerType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\PromotionType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode\TranslationType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TranslationsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
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
            ->add('title', TextType::class, ['label' => 'form.promotion_code_create.children.title.label'])
            ->add(
                'stock',
                IntegerType::class,
                ['required' => false, 'label' => 'form.promotion_code_create.children.stock.label']
            )
            ->add(
                'validUntil',
                DateTimePickerType::class,
                [
                    'required' => false,
                    'view_timezone' => $options['event']->getTimeZone(),
                    'label' => 'form.promotion_code_create.children.validUntil.label'
                ]
            )
            ->add(
                'translations',
                TranslationsType::class,
                [
                    'locales' => $options['event']->getLocales(),
                    'entry_type' => TranslationType::class,
                    'entry_options' => [],
                    'label' => false,
                ]
            )
            ->add(
                'promotions',
                CollectionType::class,
                [
                    'entry_type' => PromotionType::class,
                    'entry_options' => [
                        'event' => $options['event'],
                        'locale' => $options['locale'],
                        'label' => false,
                        'error_bubbling' => false,
                    ],
                    'allow_add' => true,
                    'allow_delete' => true,
                    'error_bubbling' => false,
                    'label' => 'form.promotion_code_create.children.promotions.label'
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setDefaults(
            [
                'data_class' => Update::class,
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'promotion_code_batch_update';
    }
}
