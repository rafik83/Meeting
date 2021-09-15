<script>
    export let poll;
</script>

<div class="poll-result">
    <p class="title">{poll.title}</p>
    <ul>
        {#each poll.choices as choice}
            <li class="choice-block">
                {#if choice.votesPercent > 0}
                    <span class="percent-background" style={`width: ${choice.votesPercent}%`}></span>
                {/if}

                <span class="choice-label">{choice.content}</span>

                {#if choice.votesPercent !== null}
                    <span class="choice-percent">{choice.votesPercent}%</span>
                {/if}
            </li>
        {/each}
    </ul>
</div>
