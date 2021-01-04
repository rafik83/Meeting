<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User;

use Proximum\Vimeet\Domain\Model\Admin;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\TypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterType extends AbstractType
{
    const FILTER_WITH_SHEET    = 'withSheet';
    const FILTER_WITHOUT_SHEET = 'withoutSheet';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', TextType::class, [
                'label'       => 'form.user_filter.children.text_search.label',
                'placeholder' => false,
                'required'    => false,
            ])
            ->add('type', TypeChoiceType::class, [
                'label'       => 'form.user_filter.children.type.label',
                'placeholder' => '',
                'event'       => $options['event'],
                'locale'      => $options['locale'],
                'user'        => $options['user'],
            ])
            ->add('participation', ChoiceType::class, [
                'label'   => 'form.user_filter.children.participation.label',
                'choices' => [
                    'admin.users.withSheet'    => self::FILTER_WITH_SHEET,
                    'admin.users.withoutSheet' => self::FILTER_WITHOUT_SHEET,
                ],
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['event', 'locale', 'user']);
        $resolver->setAllowedTypes('event', Event::class);
        $resolver->setAllowedTypes('user', Admin::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'user_filter';
    }

    /**
     * @return array
     */
    public static function getDefaultFilters()
    {
        return [
            'participation' => self::FILTER_WITH_SHEET,
        ];
    }

    /**
     * @return array
     */
    public static function getAllFilters()
    {
        return [
            self::FILTER_WITH_SHEET,
            self::FILTER_WITHOUT_SHEET,
        ];
    }
}
