<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\User;

use Proximum\Vimeet\Application\Adapter\AuthorizationCheckerAdapterInterface;
use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Command\User\Batch\Batch;
use Proximum\Vimeet\Application\Command\User\Batch\BatchCampaignResult;
use Proximum\Vimeet\Application\Query\ConditionRules\Filters\GetFiltersByTypeAndLocaleQuery;
use Proximum\Vimeet\Application\Query\User\UserEventListViews\GetUserEventListViewsQuery;
use Proximum\Vimeet\Domain\ConditionRules\Storage\RuleStorageInterface;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\UserEventView\UserEventListView;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\User\BatchType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\ValueResolver\AdminDomain;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class ListAction
{
    /** @var AuthorizationCheckerAdapterInterface */
    private $authorizationChecker;

    /** @var EngineInterface */
    private $engine;

    /** @var QueryBusInterface */
    private $queryBus;

    /** @var CommandBusInterface */
    private $commandBus;

    /** @var UrlGeneratorInterface */
    private $urlGenerator;

    /** @var RuleStorageInterface */
    private $ruleStorageInterface;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var FlashBagInterface */
    private $flashBag;

    public function __construct(
        AuthorizationCheckerAdapterInterface $authorizationChecker,
        EngineInterface $engine,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus,
        UrlGeneratorInterface $urlGenerator,
        RuleStorageInterface $ruleStorageInterface,
        FormFactoryInterface $formFactory,
        FlashBagInterface $flashBag
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->engine = $engine;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
        $this->urlGenerator = $urlGenerator;
        $this->ruleStorageInterface = $ruleStorageInterface;
        $this->formFactory = $formFactory;
        $this->flashBag = $flashBag;
    }

    public function __invoke(Request $request, Event $event, AdminDomain $adminDomain): Response
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ALLOWED_TO_ADMIN')
            || !$this->authorizationChecker->isGranted('PERMISSION_EVENT_ACCESS', $event)
        ) {
            throw new AccessDeniedException('Access denied');
        }

        $page = $request->query->getInt('page', 1);
        $locale = $event->getAvailableLocale($request->getLocale());

        if (1 === $request->query->getInt('reset')) {
            $this->ruleStorageInterface->removeRules($event, 'user');

            return new RedirectResponse(
                $this->urlGenerator->generate('admin_users_list', ['event' => $event->getId()])
            );
        }

        if ($request->query->get('rules')) {
            $this->ruleStorageInterface->saveRules($event, 'user', $request->query->get('rules'));
        }

        $rules = $this->ruleStorageInterface->getRules($event, $locale, 'user');

        $userEventListViews = $this->queryBus->handle(
            new GetUserEventListViewsQuery(
                $event,
                $page,
                $locale,
                $rules
            )
        );

        $filters = $this->queryBus->handle(new GetFiltersByTypeAndLocaleQuery($event, 'user', $request->getLocale()));

        $batch = new Batch($event, $adminDomain->getAdmin(), $locale, $rules);
        $batchForm = $this->formFactory->create(BatchType::class, $batch, [
            'ids' => $userEventListViews->paginatedResult->map(function(UserEventListView $eventListView) {
                return $eventListView->userId;
            }),
            'event' => $event,
        ]);

        $batchForm->handleRequest($request);
        if ($batchForm->isSubmitted() && $batchForm->isValid()) {
            $batch->isCampaignCreation = $batchForm->get('sendMail')->isClicked();
            $batch->isExportFormTemplate = $batchForm->get('exportFormTemplate')->isClicked();

            if ($batch->isCampaignCreation) {
                /** @var BatchCampaignResult $result */
                $result = $this->commandBus->handle($batch);

                return new RedirectResponse(
                    $this->urlGenerator->generate('admin_messaging_campaign_select_message', [
                        'event' => $event->getId(),
                        'campaign' => $result->campaign->getId(),
                    ])
                );
            }

            if ($batch->isExportFormTemplate) {

                if (!$batch->formTemplate) {
                    $this->flashBag->add('error', 'flash.admin.user.exportFormTemplate.error');
                } else {
                    $this->flashBag->add('success', 'flash.admin.user.exportFormTemplate.pending');
                    $this->commandBus->handle($batch);
                }

                return new RedirectResponse(
                    $this->urlGenerator->generate('admin_users_list', [
                        'event' => $event->getId(),
                    ])
                );
            }
        }

        return new Response(
            $this->engine->render(
                '@Admin/User/users-and-sheets-list.html.twig',
                [
                    'event' => $event,
                    'batchForm' => $batchForm->createView(),
                    'userEventListViews' => $userEventListViews,
                    'filters' => $filters,
                    'rules' => $this->ruleStorageInterface->getRulesQuery($event, 'user'),
                    'locale' => $request->getLocale(),
                ]
            )
        );
    }
}
