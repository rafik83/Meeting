<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Components\Sheet\Preview\CustomPreviewDataView;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectChoiceType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['templateObjects', 'locale']);
        $resolver->setDefaults([
            'choices'      => function (Options $options) {
                return $options['templateObjects'];
            },
            'choice_label' => function (Options $options) {
                return function ($object) use ($options) {
                    if ($object instanceof TemplateObject) {
                        return $object->getLabel($options['locale']);
                    }

                    if ($object instanceof CustomPreviewDataView) {
                        return $this->translator->trans('form.sheet_template_preview.' . $object->name, [], 'forms');
                    }

                    throw new \LogicException('Object must be instanceof CustomPreviewDataView or TemplateObject');
                };
            },
            'choice_value' => function ($object = null) {
                if (null === $object) {
                    return null;
                }

                if ($object instanceof TemplateObject) {
                    return $object->getKey();
                }

                if ($object instanceof CustomPreviewDataView) {
                    return $object->name;
                }

                throw new \LogicException('Object must be instanceof CustomPreviewDataView or TemplateObject');
            },
            'choice_translation_domain' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
