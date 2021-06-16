<script>
    import PollViewer from "./PollViewer.svelte";
    import PollAdmin from "./PollAdmin.svelte";
    import { startMainSubscriber, stopPollResultsSubscribers } from "./PollNotificationSubscriber";
    import { setExternalIndicatorElement } from "./pollExternalIndicator";
    import { setUrls } from "./api";

    export let getPollsUrl;
    export let savePollUrl;
    export let votePollUrl;
    export let setPollStatusUrl;
    export let canAdmin;
    export let happeningId;
    export let notificationProviderUrl;
    export let notificationSubscriberKey;
    export let newPollIndicator;
    export let pollTab;
    let isVisible;

    setUrls(getPollsUrl, savePollUrl, votePollUrl, setPollStatusUrl);

    startMainSubscriber(happeningId, notificationSubscriberKey, notificationProviderUrl, canAdmin);

    setExternalIndicatorElement(newPollIndicator);

    pollTab.addEventListener('activate', () => isVisible = true);
    pollTab.addEventListener('disable', () => {
        isVisible = false;
        stopPollResultsSubscribers();
    });

</script>

{#if isVisible}
    {#if canAdmin}
        <PollAdmin />
    {:else}
        <PollViewer />
    {/if}
{/if}
