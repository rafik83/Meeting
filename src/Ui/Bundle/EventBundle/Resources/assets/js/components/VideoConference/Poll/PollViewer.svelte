<script>
    import { onMount } from "svelte";
    import { trans } from "./Translations";
    import { isLoading, polls } from "./PollsStore";
    import PollViewerForm from "./PollViewerForm.svelte";
    import PollResult from "./PollResult.svelte";
    import { updateExternalIndicatorElement } from "./pollExternalIndicator";
    import { loadPolls } from "./api";

    onMount(async () => {
        isLoading.enable();
        await loadPolls();
        isLoading.disable();
    });

    polls.subscribe((list) => {
        const unvotedPolls = list.filter((poll) => poll.canVote).length;
        updateExternalIndicatorElement(unvotedPolls);
    });
</script>

<div class:hide={!$isLoading} class="loadingDiv">
    <span class="loadingIcon"></span>
    <span>{trans('loading')}</span>
</div>

<div class="polls-scroll polls-list">
    {#if $polls.length === 0}
        <div class:hide={$isLoading} class="poll-empty">
            <p>{trans('noPoll')}</p>
        </div>
    {/if}
    {#each $polls as poll}
        {#if poll.canVote}
            <PollViewerForm {poll} />
        {:else}
            <PollResult {poll} />
        {/if}
    {/each}
</div>
