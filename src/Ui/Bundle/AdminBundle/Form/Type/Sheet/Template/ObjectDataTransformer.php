<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Sheet\Template;

use Proximum\Vimeet\Application\Components\Sheet\Preview\CustomPreviewData;
use Proximum\Vimeet\Application\Components\Sheet\Preview\CustomPreviewDataView;
use Proximum\Vimeet\Domain\Template\TemplateData;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Symfony\Component\Form\DataTransformerInterface;

class ObjectDataTransformer implements DataTransformerInterface
{
    /**
     * @var TemplateData
     */
    private $templateData;

    /**
     * ObjectDataTransformer constructor.
     *
     * @param TemplateData $templateData
     */
    public function __construct(TemplateData $templateData)
    {
        $this->templateData = $templateData;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($keys)
    {
        return array_map(
            function ($key) {
                $customPreviewDataView = CustomPreviewData::getCustomPreviewDataViewByName($key);

                if (null !== $customPreviewDataView) {
                    return $customPreviewDataView;
                }

                return $this->templateData->getObject($key);
            },
            $keys
        );
    }

    /**
     * @param array $objects of TemplateObject or CustomPreviewDataView
     *
     * @return array of string
     */
    public function reverseTransform($objects)
    {
        $serializedObject = [];

        foreach ($objects as $object) {
            if ($object instanceof TemplateObject) {
                $serializedObject[] = $object->getKey();
            } elseif ($object instanceof CustomPreviewDataView) {
                $serializedObject[] = $object->name;
            } else {
                throw new \LogicException('object must be instanceof CustomPreviewDataView or TemplateObject');
            }
        }

        return $serializedObject;
    }
}
