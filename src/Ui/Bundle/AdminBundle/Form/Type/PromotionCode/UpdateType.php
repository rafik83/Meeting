<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\PromotionCode;

use Proximum\Vimeet\Application\Command\PromotionCode\Update;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateType extends PromotionCodeType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $canUpdatePromotions = $options['can_update_promotions'];

        $builder
            ->add('promotions', CollectionType::class, [
                'entry_type'     => PromotionType::class,
                'entry_options'  => [
                    'event' => $options['event'],
                    'label' => false,
                    'error_bubbling' => false,
                    'locale' => $options['locale'],
                    'can_update_promotions' => $canUpdatePromotions,
                ],
                'allow_add' => $canUpdatePromotions,
                'allow_delete' => $canUpdatePromotions,
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

        $resolver->setRequired('can_update_promotions');
        $resolver->setDefaults([
            'data_class' => Update::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'promotion_code_update';
    }
}
