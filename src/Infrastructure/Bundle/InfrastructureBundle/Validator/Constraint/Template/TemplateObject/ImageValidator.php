<?php

namespace Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObject;

use Proximum\Vimeet\Domain\Model\Product;
use Proximum\Vimeet\Domain\Package\Product\TemplateProductGuesser;
use Proximum\Vimeet\Domain\Template\TemplateObject;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Validator\Constraint\Template\TemplateObjectValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

class ImageValidator extends TemplateObjectValidator
{
    /**
     * @var TemplateProductGuesser
     */
    private $templateProductGuesser;

    /**
     * @var TemplateObject\BuyableIncludedProductGuesser
     */
    private $buyableIncludedProductGuesser;

    /**
     * @param TemplateProductGuesser                       $templateProductGuesser
     * @param TemplateObject\BuyableIncludedProductGuesser $buyableIncludedProductGuesser
     */
    public function __construct(
        TemplateProductGuesser $templateProductGuesser,
        TemplateObject\BuyableIncludedProductGuesser $buyableIncludedProductGuesser
    ) {
        $this->templateProductGuesser = $templateProductGuesser;
        $this->buyableIncludedProductGuesser = $buyableIncludedProductGuesser;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof TemplateObject\Image) {
            $this->checkRequired($value, $constraint);
            $this->checkHasPayableOption($value);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function checkRequired(TemplateObject $object, Constraint $constraint)
    {
        if ($object instanceof TemplateObject\Image
            && true === $object->getOption('required')
            && $object instanceof TemplateObject\ContentObjectInterface
        ) {
            $content = $object->getImage();

            if (null === $content) {
                $content = $object->getFile();
            }

            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath($constraint->key)
                ->validate($content, new NotBlank());
        }
    }

    /**
     * @param TemplateObject $object
     */
    protected function checkHasPayableOption(TemplateObject $object)
    {
        if ($this->templateProductGuesser->hasPayableOption($object)
            && !$this->buyableIncludedProductGuesser->hasBuyableIncludedProduct($object)
        ) {
            $this->context
                ->getValidator()
                ->inContext($this->context)
                ->atPath('selectedProduct')
                ->validate($object->getSelectedProduct(), new NotBlank());

            $productIds = array_map(function (Product $product) {
                return $product->getId();
            }, $object->getBuyableProducts());

            if (null !== $object->getSelectedProduct() && !\in_array($object->getSelectedProduct(), $productIds, true)) {
                $this->context->buildViolation('validators.sheet.object.productNotValid')->atPath('selectedProduct')->addViolation();
            }
        }
    }
}
