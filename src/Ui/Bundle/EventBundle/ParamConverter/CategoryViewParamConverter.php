<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter;

use Proximum\Vimeet\Domain\Repository\CategoryRepositoryInterface;
use Proximum\Vimeet\Domain\View\CategoryView;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryViewParamConverter implements ParamConverterInterface
{
    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $id     = $request->attributes->get('categoryView');
        $locale = $request->getLocale();

        $category = $this->categoryRepository->getCategoryViewById($id, $locale);

        if (null === $category) {
            throw new NotFoundHttpException('Type not found');
        }

        $request->attributes->set($configuration->getName(), $category);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return CategoryView::class === $configuration->getClass();
    }
}
