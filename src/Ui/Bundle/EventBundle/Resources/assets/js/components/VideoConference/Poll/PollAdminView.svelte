<script>
    import { isLoading } from "./PollsStore";
    import { loadPolls, setPollStatus } from "./api";
    import { STATUS_HIDDEN, STATUS_PUBLISHED } from "./status";
    import { trans } from "./Translations";
    import PollResult from "./PollResult.svelte";

    export let poll;

    const enablePoll = () => {
        putStatusAndRefresh(STATUS_PUBLISHED);
    }

    const disablePoll = () => {
        putStatusAndRefresh(STATUS_HIDDEN);
    }

    const putStatusAndRefresh = async (status) => {
        isLoading.enable();

        await setPollStatus(poll.id, status);
        await loadPolls();

        isLoading.disable();
    }

</script>
<div class="polls-list">
    <PollResult {poll}/>
</div>

{#if poll.totalVotes > 0}
    <small class="poll-vote-right">{trans('votesCount').replace('%count%', poll.totalVotes)}</small>
{/if}

<div class="poll-center">
    {#if poll.status === STATUS_PUBLISHED}
        <button on:click={disablePoll} class="btn btn-primary poll-action">{trans('buttonDisableQuestion')}</button>
        <small>{trans('questionPublished')}</small>
    {:else}
        <button on:click={enablePoll} class="btn btn-primary poll-action">{trans('buttonEnableQuestion')}</button>
        <small>{trans('questionHidden')}</small>
    {/if}
</div>
