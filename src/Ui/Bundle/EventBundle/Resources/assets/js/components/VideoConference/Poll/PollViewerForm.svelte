<script>
    import { trans } from "./Translations";
    import {isLoading, polls} from "./PollsStore";
    import {startPollResultsSubscriber} from "./PollNotificationSubscriber";
    import { loadPolls, votePoll } from "./api";

    export let poll;

    let selectedChoices = [];
    let selectedChoice;
    let disabled = false;
    let canVote = false;

    $: canVote = selectedChoices.length || selectedChoice;

    const save = async () => {
        isLoading.enable();
        disabled = true;

        let choicesId;
        if (poll.multipleChoice) {
            choicesId = selectedChoices;
        } else {
            choicesId = [selectedChoice];
        }

        const result = await votePoll(poll.id, choicesId);

        if (result && result.poll) {
            startPollResultsSubscriber(result.poll);
            polls.updatePoll(result.poll);
        } else {
            disabled = false;
            selectedChoices = [];
            loadPolls();
        }

        isLoading.disable();
    }

</script>
    <div class="poll">
        <p class="title">{poll.title}</p>
        {#each poll.choices as choice}
            {#if poll.multipleChoice === true}
                <label for="poll-{choice.id}-multiple-choices">
                    <input id="poll-{choice.id}-multiple-choices" type=checkbox disabled={disabled}
                           bind:group={selectedChoices} value={choice.id}/>{choice.content}
                </label>
            {:else}
                <label>
                    <input name="poll-unique-choices" type=radio disabled={disabled} bind:group={selectedChoice} value={choice.id}/>
                    {choice.content}
                </label>
            {/if}
        {/each}
        {#if poll.multipleChoice === true}
            <small>{trans("choicesMentionMultiple")}</small>
        {/if}
        <div class="button-container">
            <div class="poll-viewer-vote">
                <button type="submit" disabled={disabled || !canVote} on:click={save} class="btn btn-primary poll-action">{trans("submitVote")}</button>
            </div>
            <small>{trans("seeResults")}</small>
        </div>
    </div>
