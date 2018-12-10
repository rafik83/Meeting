<?php

/*
 * This file is part of the Proximum Vimeet project.
 *
 * Copyright (C) Proximum
 *
 * @author Elao <contact@elao.com>
 */

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Domain\Template\TemplateObject\MultiUploadCollectionObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MultiUploadDataType extends AbstractType
{
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var MultiUploadCollectionObject $uploadCollection */
        $uploadCollection = $options['object'];

        $builder
            ->add('uploads', CollectionType::class, [
                'entry_type' => UploadDataType::class,
                'entry_options' => [
                    'label' => false,
                    'collection' => $options['data'],
                    'required' => $uploadCollection->getRequired(),
                    'titlePlaceholder' => $uploadCollection->getTitlePlaceholder(),
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'label' => false,
                'help' => $this->getHelp($uploadCollection->getFormats()),
                'max' => $uploadCollection->getMax(),
            ]);
    }

    private function getHelp(array $formats): string
    {
        return $this->translator->transChoice(
            'common.required_formats',
            \count($formats),
            ['%format%' => implode(', ', $formats)]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['locale', 'object']);
        $resolver->setDefaults([
            'data_class'  => MultiUploadCollectionObject::class,
            'placeholder' => null,
        ]);
    }
}
