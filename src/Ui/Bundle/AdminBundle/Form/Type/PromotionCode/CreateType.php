<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode;

use Proximum\Vimeet\Application\Command\PromotionCode\Create;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateType extends PromotionCodeType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('promotions', CollectionType::class, [
                'entry_type' => PromotionType::class,
                'entry_options' => [
                    'error_bubbling' => false,
                    'event' => $options['event'],
                    'label' => false,
                    'locale' => $options['locale'],
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'error_bubbling' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Create::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'promotion_code_create';
    }
}
