<script>
    import { STATUS_DRAFT } from "./status";
    import { trans } from "./Translations";
    import PollAdminForm from "./PollAdminForm.svelte";

    let newPoll;

    const add = () => {
        newPoll = {
            title: '',
            choices: [{content: ''}, {content: ''}],
            multipleChoice: false,
            status: STATUS_DRAFT
        };
    }
</script>

<div class="poll-center">
    <button on:click={add} disabled={!!newPoll} class="btn btn-primary poll-action">{trans('buttonAddQuestion')}</button>
</div>

{#if newPoll}
    <PollAdminForm poll={newPoll} on:published on:saved={() => (newPoll = null)} />
{/if}
