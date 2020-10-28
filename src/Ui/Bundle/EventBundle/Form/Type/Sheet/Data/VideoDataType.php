<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Product\IdToProductTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class VideoDataType extends AbstractType
{
    /** @var IdToProductTransformer */
    private $idToProductTransformer;

    /** @var TemplateProductGuesser */
    private $templateProductGuesser;

    /** @var BuyableObjectResolver */
    private $buyableObjectResolver;

    /** @var TemplateObject\BuyableIncludedProductGuesser */
    private $buyableIncludedProductGuesser;

    public function __construct(
        IdToProductTransformer $idToProductTransformer,
        TemplateProductGuesser $templateProductGuesser,
        BuyableObjectResolver $buyableObjectResolver,
        TemplateObject\BuyableIncludedProductGuesser $buyableIncludedProductGuesser
    ) {
        $this->idToProductTransformer = $idToProductTransformer;
        $this->templateProductGuesser = $templateProductGuesser;
        $this->buyableObjectResolver  = $buyableObjectResolver;
        $this->buyableIncludedProductGuesser = $buyableIncludedProductGuesser;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var TemplateObject\Video $video */
        $video = $options['object'];

        $builder->add('file', FileType::class, [
            'label' => true === $options['showLabel'] ? $video->getLabel($options['locale']) : false,
            'required' => $video->getOption('required'),
            'attr' => [
                'accept' => implode(', ', TemplateObject\Video::supportedMimeType()),
            ],
            'constraints' => [
                new File([
                    'mimeTypes' => TemplateObject\Video::supportedMimeType(),
                    'maxSize' => '100M',
                ]),
            ],
        ]);

        if ($this->templateProductGuesser->hasPayableOption($video)
            && !$this->buyableIncludedProductGuesser->hasBuyableIncludedProduct($video)
        ) {
            $selectedRadio = $this->buyableObjectResolver->getSelectedProduct($video);

            $builder->add('selectedProduct', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choice_name' => 'id',
                'choices' => $video->getBuyableProducts(),
                'required' => true,
                'data' => $selectedRadio,
                'attr' => ['required' => true],
            ]);
            $builder->get('selectedProduct')->addModelTransformer($this->idToProductTransformer);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['object', 'locale']);
        $resolver->setAllowedTypes('object', TemplateObject\Video::class);
        $resolver->setDefaults([
            'label' => false,
            'showLabel' => false,
            'data_class' => TemplateObject\Video::class,
            'placeholder' => null,
            'help' => null,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'sheet_video_data';
    }
}
