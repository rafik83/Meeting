<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Handler\Sheet;

use Proximum\Vimeet\Application\Adapter\RouterInterface;
use Proximum\Vimeet\Application\Components\Navigation\Route;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CreateObjectFormHandler
{
    /** @var RouterInterface */
    private $router;

    /** @var FormFactoryInterface */
    private $formFactory;

    public function __construct(
        FormFactoryInterface $formFactory,
        RouterInterface $router
    ) {
        $this->router = $router;
        $this->formFactory = $formFactory;
    }

    public function handle(CreateObjectForm $createObjectForm): FormInterface
    {
        $object = $createObjectForm->templateObject;
        $locale = $createObjectForm->locale;

        $types = [
            'editable-text' => Data\EditableTextDataType::class,
            'button-link' => Data\ButtonLinkDataType::class,
            'media' => Data\MediaCollectionDataType::class,
            'collection' => Data\ItemCollectionDataType::class,
            'nomenclature' => Data\NomenclatureDataType::class,
            'image' => Data\ImageDataType::class,
            'tags' => Data\ItemCollectionDataType::class,
            'multi-upload' => Data\MultiUploadDataType::class,
            'video' => Data\VideoDataType::class,
        ];

        if (!isset($types[$object->getType()])) {
            throw new NotFoundHttpException('No form found for this object');
        }

        return $this->formFactory->create($types[$object->getType()], $object, [
            'action' => $this->router->generate(
                Route::SHEET_UPDATE,
                [
                    'sheet' => $object->getSheet()->getId(),
                    'locale' => $locale,
                    'key' => $createObjectForm->key
                ]
            ),
            'submit' => true,
            'locale' => $locale,
            'help' => $object->getHelp(),
            'placeholder' => $object->getPlaceholder(),
            'object' => $object,
            'required' => $object->getRequired(),
        ]);
    }
}
