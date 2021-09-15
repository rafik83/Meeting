<?php

namespace Proximum\Vimeet\Ui\Bundle\EventBundle\ParamConverter;

use Proximum\Vimeet\Domain\Model\Sheet\Group;
use Proximum\Vimeet\Domain\Repository\Sheet\GroupRepositoryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GroupParamConverter implements ParamConverterInterface
{
    /** @var GroupRepositoryInterface */
    private $groupRepository;

    /**
     * @param GroupRepositoryInterface $groupRepository
     */
    public function __construct(GroupRepositoryInterface $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        /** @var null|EventDomain $eventDomain */
        $eventDomain = $request->attributes->get('eventDomain');
        $group = $this->groupRepository->getById($request->attributes->get('sheetGroup'));

        if (null === $group) {
            throw new NotFoundHttpException('Group not found');
        }

        if (null !== $eventDomain && $eventDomain->getEvent() !== $group->getEvent()) {
            throw new NotFoundHttpException('Group not found in that event');
        }

        $request->attributes->set($configuration->getName(), $group);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ParamConverter $configuration)
    {
        return Group::class === $configuration->getClass();
    }
}
