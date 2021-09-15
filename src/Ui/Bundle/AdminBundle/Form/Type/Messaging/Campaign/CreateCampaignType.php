<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign;

use Proximum\Vimeet\Application\Command\Messaging\Campaign\Create;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateCampaignType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label'    => 'form.sheet.messaging_campaign.create.name.label',
                'required' => true,
            ])
            ->add('sheetIds', ChoiceType::class, [
                'choices'            => $options['sheet_ids'],
                'choice_name'        => function ($id) {
                    return $id;
                },
                'required'           => true,
                'expanded'           => true,
                'multiple'           => true,
                'label'              => false,
                'translation_domain' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('sheet_ids')
            ->setAllowedTypes('sheet_ids', ['array'])
            ->setDefaults([
                'data_class' => Create::class,
            ])
        ;
    }
}
