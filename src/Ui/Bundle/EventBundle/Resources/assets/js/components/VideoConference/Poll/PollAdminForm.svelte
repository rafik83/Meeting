<script>
    import { createEventDispatcher } from "svelte";
    import { isLoading } from "./PollsStore";
    import { trans } from "./Translations";
    import { loadPolls, savePoll } from "./api";

    const SHOW_FORM = 'form';
    const SHOW_CONFIRM = 'confirm';
    const SHOW_RESPONSE_DELETE = 'response_delete';

    export let poll;

    let show = SHOW_FORM;
    let isValid = false;
    let choiceToRemove;

    const dispatch = createEventDispatcher();

    $: isValid = !!poll.title && poll.choices.length >= 2 && poll.choices.every((choice) => !!choice.content);

    const addChoice = () => {
        poll.choices = [...poll.choices, {content: ''}];
    }

    const save = () => {
        poll.publish = false;
        postAndRefresh(poll);
    }

    const confirmPublish = () => {
        show = SHOW_CONFIRM;
    }

    const publish = async () => {
        poll.publish = true;
        await postAndRefresh(poll);
        dispatch('published', poll);
    }

    const postAndRefresh = async (payload) => {
        isLoading.enable();

        await savePoll(payload);
        dispatch('saved', poll);

        await loadPolls();

        isLoading.disable();
    }

    const removeConfirm = (choice) => {
        show = SHOW_RESPONSE_DELETE;
        choiceToRemove = choice;
    }

    const removeChoice = async () => {
        isLoading.enable();
        poll.choices = poll.choices.filter((choice) => choice !== choiceToRemove);
        isLoading.disable();
        show = SHOW_FORM;
    }
</script>

<div class:hide={show !== SHOW_FORM} class="poll-form">
    <div class="poll-admin-form">
        <textarea id="poll-{poll.id}-title" bind:value={poll.title} placeholder={trans('questionPlaceholder')}></textarea>
    </div>

    <div class="poll-admin-choices">
        <div>
            <input id="poll-{poll.id}-multiple-choices-0" type=radio bind:group={poll.multipleChoice} value={false} />
            <label for="poll-{poll.id}-multiple-choices-0">{trans('choicesSingle')}</label>
        </div>
        <div>
            <input id="poll-{poll.id}-multiple-choices-1" type="radio" bind:group={poll.multipleChoice} value={true} />
            <label for="poll-{poll.id}-multiple-choices-1">{trans('choicesMultiple')}</label>
        </div>
    </div>

    {#each poll.choices as choice, index}
        <div class="add-response-form">
            <textarea class={index >= 2 ? 'not-full' :''}
                id="poll-{poll.id}-choice-{choice.id}"
                bind:value={choice.content}
                placeholder={trans('choicePlaceholder').replace('%index%', index+1)}
            ></textarea>
            {#if index >= 2}
                <div class="poll-container-close">
                    <button on:click={() => removeConfirm(choice)}><i class="icon-Fermer_3"></i></button>
                </div>
            {/if}
        </div>
    {/each}
    <div class="add-response">
        <button on:click={addChoice} class="btn poll-action-response">{trans('buttonAddChoice')}</button>
    </div>

    <div class="poll-admin-form-submit">
        <button disabled={!isValid} on:click={save} class="btn btn-primary poll-action">{trans('buttonSave')}</button>
        <button disabled={!isValid} on:click={confirmPublish} class="btn btn-primary poll-action">{trans('buttonPublish')}</button>
    </div>
</div>

<div class:hide={show !== SHOW_CONFIRM} class="poll-confirm">
    <div class="margin">
        <i class="poll-icon publish icon-Alerter_1"></i>
    </div>
    {@html trans('publishConfirm').replace("\n", '<br />')}
    <div class="margin">
        <button on:click={() => show=SHOW_FORM} class="btn btn-gray poll-action">{trans('buttonCancel')}</button>
        <button on:click={publish} class="btn btn-primary poll-action">{trans('buttonConfirm')}</button>
    </div>
</div>

<div class:hide={show !== SHOW_RESPONSE_DELETE} class="poll-confirm">
    <div class="margin">
        <i class="poll-icon circle delete icon-Effacer"></i>
    </div>
    {@html trans('responseDeleteConfirm').replace("\n", '<br />')}
    <div class="margin">
        <button on:click={() => show=SHOW_FORM} class="btn btn-gray poll-action">{trans('buttonCancel')}</button>
        <button on:click={removeChoice} class="btn btn-primary poll-action">{trans('buttonConfirm')}</button>
    </div>
</div>
