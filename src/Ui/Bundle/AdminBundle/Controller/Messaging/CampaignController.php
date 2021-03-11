<?php

namespace Proximum\Vimeet\Ui\Bundle\AdminBundle\Controller\Messaging;

use Proximum\Vimeet\Application\Adapter\CommandBusInterface;
use Proximum\Vimeet\Application\Adapter\QueryBusInterface;
use Proximum\Vimeet\Application\Adapter\TranslatorInterface;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\Create;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\SelectMessage;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\SelectRecipients;
use Proximum\Vimeet\Application\Command\Messaging\Campaign\Send;
use Proximum\Vimeet\Application\Query\ConditionRules\Filters\GetFiltersByTypeAndLocaleQuery;
use Proximum\Vimeet\Application\Query\ConditionRules\Rules\GetConditionRulesQuery;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\Assembler\CampaignAssembler;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\ListViewQuery;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\SheetListView;
use Proximum\Vimeet\Application\Query\Messaging\Campaign\SheetListViewQuery;
use Proximum\Vimeet\Domain\Model\Event;
use Proximum\Vimeet\Domain\Model\Messaging\Campaign;
use Proximum\Vimeet\Infrastructure\Bundle\InfrastructureBundle\Service\FilterSummary;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign\CreateCampaignType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign\SelectMessageType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign\SelectRecipientsType;
use Proximum\Vimeet\Ui\Bundle\AdminBundle\Form\Type\Messaging\Campaign\TargetFilterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CampaignController extends AbstractController
{
    private CampaignAssembler $campaignAssembler;
    private FilterSummary $filterSummary;
    private TranslatorInterface $translator;
    private CsrfTokenManagerInterface $tokenManager;
    private QueryBusInterface $queryBus;
    private CommandBusInterface $commandBus;

    public function __construct(
        CampaignAssembler $campaignAssembler,
        FilterSummary $filterSummary,
        TranslatorInterface $translator,
        CsrfTokenManagerInterface $tokenManager,
        QueryBusInterface $queryBus,
        CommandBusInterface $commandBus
    ) {
        $this->campaignAssembler = $campaignAssembler;
        $this->filterSummary = $filterSummary;
        $this->translator = $translator;
        $this->tokenManager = $tokenManager;
        $this->queryBus = $queryBus;
        $this->commandBus = $commandBus;
    }

    /**
     * First step of a messaging campaign creation: display the list of active sheets for the current event
     * and enable the user to select some criteria to filter that list (campaign's "targets").
     */
    public function selectSheetsAction(Request $request, Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $locale = $event->getAvailableLocale($request->getLocale());

        $filterForm = $this->createForm(TargetFilterType::class, [], [
            'event' => $event,
            'locale' => $locale,
            'user' => $this->getUser(),
            'method' => 'get',
            'csrf_protection' => false,
        ])->add('submit', SubmitType::class, [
            'label' => 'form.sheet_comment.children.filter.label',
            'attr' => ['class' => 'btn btn-default'],
        ]);

        $filterForm->handleRequest($request);

        if (!$filterForm->isSubmitted()) {
            // if $createCampaignForm is submitted, we need to submit data to $filterForm too,
            // to normalize filter values (especially booleans)
            $filterForm->submit($request->query->get('targetting', []));
        }
        $filters = $filterForm->getData();

        $rules = null;
        if ($request->query->get('rules')) {
            $rules = $this->queryBus->handle(
                new GetConditionRulesQuery(
                    $event,
                    $locale,
                    json_decode($request->query->get('rules'), true)
                )
            );
        }

        $query = new SheetListViewQuery($event, $filters, $locale, $rules);
        $sheets = $this->queryBus->handle($query);
        $filterFormView = $filterForm->createView();

        $createCampaignCommand = new Create($event, $request->get($filterForm->getName(), []));
        $createCampaignForm = $this->createForm(CreateCampaignType::class, $createCampaignCommand, [
            'sheet_ids' => array_map(static function (SheetListView $sheet) { return $sheet->id; }, $sheets),
            'action' => $request->getUri(),
        ]);

        if ($createCampaignForm->handleRequest($request)->isSubmitted() && $createCampaignForm->isValid()) {
            /** @var Campaign $campaign */
            $campaign = $this->commandBus->handle($createCampaignCommand);
            $this->addFlash('success', 'flash.admin.messaging.campaign.create.success');

            return $this->redirectToRoute('admin_messaging_campaign_select_recipients', [
                'event' => $event->getId(),
                'campaign' => $campaign->getId(),
            ]);
        }

        $filters = $this->queryBus->handle(new GetFiltersByTypeAndLocaleQuery($event, 'sheet', $request->getLocale()));

        return $this->render('AdminBundle:Messaging\Campaign:select_sheets.html.twig', [
            'event' => $event,
            'sheets' => $sheets,
            'filters' => $filters,
            'locale' => $request->getLocale(),
            'rules' => $request->query->get('rules'),
            'filter_form' => $filterFormView,
            'filters_summary' => $this->filterSummary->getFilters($filterFormView, $filters, $event, $locale),
            'create_campaign_form' => $createCampaignForm->createView(),
        ]);
    }

    /**
     * Second step of a messaging campaign creation: select recipients (sheet owners, participants and/or billing contacts).
     */
    public function selectRecipientsAction(Request $request, Event $event, Campaign $campaign): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        if ($event !== $campaign->getEvent()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(SelectRecipientsType::class, new SelectRecipients($campaign));
        $form->add('submit', SubmitType::class, [
            'label' => 'form.sheet.messaging_campaign.recipients.submit.label',
            'attr'  => ['class' => 'btn btn-primary'],
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($form->getData());
            $this->addFlash('success', 'flash.admin.messaging.campaign.recipients.success');

            return $this->redirectToRoute('admin_messaging_campaign_select_message', [
                'event'    => $event->getId(),
                'campaign' => $campaign->getId(),
            ]);
        }

        $campaignView = $this->campaignAssembler->assemble($campaign);

        return $this->render('AdminBundle:Messaging\Campaign:select_recipients.html.twig', [
            'event'    => $event,
            'form'     => $form->createView(),
            'campaign' => $campaignView,
        ]);
    }

    /**
     * Third step of messaging campaign creation: select message.
     */
    public function selectMessageAction(Request $request, Event $event, Campaign $campaign): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        if ($event !== $campaign->getEvent()) {
            throw new AccessDeniedException();
        }

        $selectMessage = new SelectMessage($campaign);
        $form          = $this->createForm(SelectMessageType::class, $selectMessage, [
            'event' => $event,
        ]);
        $form->add('submit', SubmitType::class, [
            'label' => 'form.sheet.messaging_campaign.message.submit.label',
            'attr'  => ['class' => 'btn btn-primary'],
        ]);

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->commandBus->handle($selectMessage);
            $this->addFlash('success', 'flash.admin.messaging.campaign.message.success');

            return $this->redirectToRoute('admin_messaging_campaign_list', [
                'event' => $event->getId(),
            ]);
        }

        return $this->render('AdminBundle:Messaging\Campaign:select_message.html.twig', [
            'event' => $event,
            'form'  => $form->createView(),
        ]);
    }

    /**
     * Display all messaging campaigns for a given event.
     */
    public function listAction(Event $event): Response
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        $query     = new ListViewQuery($event);
        $campaigns = $this->queryBus->handle($query);

        return $this->render('AdminBundle:Messaging\Campaign:list.html.twig', [
            'event'     => $event,
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Sends a given messaging Campaign.
     */
    public function sendAction(Request $request, Event $event, Campaign $campaign): RedirectResponse
    {
        $this->denyAccessUnlessGranted('PERMISSION_EVENT_ACCESS', $event);
        $this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_ADMIN');

        if ($event !== $campaign->getEvent()) {
            throw new AccessDeniedException();
        }

        $url = $this->generateUrl('admin_messaging_campaign_list', ['event' => $event->getId()]);

        // Validate CSRF token
        if (!$this->isTokenValid($request)) {
            $this->addFailureMessage($campaign, 'flash.messaging.campaign.send.failure.invalid_csrf');

            return $this->redirect($url);
        }

        $this->commandBus->handle(new Send($campaign));

        // Add flash
        $this->addFlash('success', 'flash.messaging.campaign.send.success');

        return $this->redirect($url);
    }

    private function isTokenValid(Request $request): bool
    {
        if (!$request->request->has('_token')) {
            return false;
        }

        $token = new CsrfToken('send_campaign', $request->request->get('_token'));

        return $this->tokenManager->isTokenValid($token);
    }

    private function addFailureMessage(Campaign $campaign, string $reason): void
    {
        $message    = $this->translator->trans(
            'flash.messaging.campaign.send.failure',
            ['%reason%' => $this->translator->trans($reason, ['%title%' => $campaign->getTitle()], 'flashes')],
            'flashes'
        );

        $this->addFlash('error', $message);
    }
}
