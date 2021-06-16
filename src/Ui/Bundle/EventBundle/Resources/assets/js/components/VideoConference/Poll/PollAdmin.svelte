<script>
    import { onMount } from "svelte";
    import { trans } from "./Translations";
    import { isLoading, polls } from "./PollsStore";
    import { loadPolls } from "./api";
    import PollAdd from "./PollAdd.svelte";
    import PollAdminForm from "./PollAdminForm.svelte";
    import PollAdminView from "./PollAdminView.svelte";
    import { STATUS_DRAFT } from "./status";

    const TAB_CREATE = "create";
    const TAB_VIEW = "view";

    let tab = TAB_CREATE;
    let notDraftPolls;

    $: notDraftPolls = $polls.filter((p) => p.status !== STATUS_DRAFT);

    onMount(async () => {
        isLoading.enable();
        await loadPolls();
        isLoading.disable();
    });
</script>

<div class="submenu-poll-admin">
    <button class:selected={tab === TAB_CREATE} class="btn poll-submenu-tab" on:click={() => (tab = TAB_CREATE)}>{trans('tabCreate')}</button>
    <button class:selected={tab === TAB_VIEW} class="btn poll-submenu-tab" on:click={() => (tab = TAB_VIEW)}>{trans('tabView')}</button>
</div>

<div class:hide={!$isLoading} class="loadingDiv">
    <span class="loadingIcon"></span>
    <span>{trans('loading')}</span>
</div>

{#if tab === TAB_CREATE}
    <div class="polls-scroll">
        <PollAdd on:published={() => (tab = TAB_VIEW)} />

        {#each $polls.filter((p) => p.status === STATUS_DRAFT) as poll}
            <PollAdminForm {poll} on:published={() => (tab = TAB_VIEW)} />
        {/each}
    </div>
{/if}

{#if tab === TAB_VIEW}
    <div class="poll-admin-view">
        {#if notDraftPolls.length === 0 && !$isLoading}
            <div class="poll-empty">
                <p>{trans('pollsEmpty')}</p>
            </div>
        {/if}

        {#each notDraftPolls as poll}
            <PollAdminView {poll} />
        {/each}
    </div>
{/if}
