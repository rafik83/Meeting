<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Type;

use Proximum\Vimeet\Domain\View\TypeView;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\Markdown;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeChoiceType extends AbstractType
{
    /**
     * @var Markdown
     */
    private $markdown;

    /**
     * @param Markdown $markdown
     */
    public function __construct(Markdown $markdown)
    {
        $this->markdown = $markdown;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label'              => false,
                'choices'            => $options['typeViews'],
                'expanded'           => true,
                'required'           => true,
                'choice_label'       => 'title',
                'choice_value'       => 'id',
                'attr'               => ['data-choice-description' => '#type-description'],
                'translation_domain' => false,
                'choice_attr'        => function (TypeView $typeView) {
                    return ['data-description' => $this->markdown->toHtml($typeView->description)];
                },
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['typeViews']);
        $resolver->setAllowedTypes('typeViews', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'type_choice';
    }
}
