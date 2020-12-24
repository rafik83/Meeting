<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Type\Sheet\Data;

use Proximum\Vimeet\Domain\Cart\BuyableObjectResolver;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Domain\Template\TemplateObject\BuyableIncludedProductGuesser;
use Proximum\Vimeet\Domain\Template\TemplateObject\MediaCollection;
use Proximum\Vimeet\Ui\Bundle\EventBundle\Form\Transformer\Sheet\Data\Product\IdToProductTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaCollectionDataType extends AbstractType
{
    /** @var IdToProductTransformer */
    private $idToProductTransformer;

    /** @var TemplateProductGuesser */
    private $templateProductGuesser;

    /** @var BuyableObjectResolver */
    private $buyableObjectResolver;

    /** @var BuyableIncludedProductGuesser */
    private $buyableIncludedProductGuesser;

    /**
     * @param IdToProductTransformer        $idToProductTransformer
     * @param TemplateProductGuesser        $templateProductGuesser
     * @param BuyableObjectResolver         $buyableObjectResolver
     * @param BuyableIncludedProductGuesser $buyableIncludedProductGuesser
     */
    public function __construct(
        IdToProductTransformer $idToProductTransformer,
        TemplateProductGuesser $templateProductGuesser,
        BuyableObjectResolver $buyableObjectResolver,
        BuyableIncludedProductGuesser $buyableIncludedProductGuesser
    ) {
        $this->idToProductTransformer = $idToProductTransformer;
        $this->templateProductGuesser = $templateProductGuesser;
        $this->buyableObjectResolver  = $buyableObjectResolver;
        $this->buyableIncludedProductGuesser = $buyableIncludedProductGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var MediaCollection $media */
        $media = $options['object'];
        $builder
            ->add('medias', CollectionType::class, [
                'entry_type'    => MediaDataType::class,
                'entry_options' => [
                    'label'       => false,
                    'collection'  => $options['data'],
                    'required'    => false,
                    'linkPlaceholder' => $media->getLinkPlaceholder(),
                    'titlePlaceholder' => $media->getTitlePlaceholder(),
                ],
                'allow_add'     => true,
                'allow_delete'  => true,
                'label'         => false,
                'max'           => $options['data']->getMax(),
            ]);

        if ($this->templateProductGuesser->hasPayableOption($media)
            && !$this->buyableIncludedProductGuesser->hasBuyableIncludedProduct($media)
        ) {
            $selectedRadio = $this->buyableObjectResolver->getSelectedProduct($media);

            $builder->add('selectedProduct', ChoiceType::class, [
                'expanded'    => true,
                'multiple'    => false,
                'choice_name' => 'id',
                'choices'     => $media->getBuyableProducts(),
                'required'    => true,
                'data'        => $selectedRadio,
            ]);
            $builder->get('selectedProduct')->addModelTransformer($this->idToProductTransformer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['locale', 'object']);
        $resolver->setDefaults([
            'data_class'  => MediaCollection::class,
            'placeholder' => null,
            'help'        => null,
            'attr'        => [
                'data-product-selector' => (int) true,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sheet_media_collection_data';
    }
}
