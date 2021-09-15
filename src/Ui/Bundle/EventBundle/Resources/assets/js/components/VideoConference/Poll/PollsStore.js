import { get, writable } from 'svelte/store';

const PollsStore = () => {
    const {subscribe, update, set} = writable([]);

    return {
        subscribe,
        setPolls: (polls) => set(polls),
        hasPoll: (pollId) => get(polls).some((pollItem) => pollItem.id === pollId),
        prependPoll: (poll) => update((currentPolls) => [poll, ...currentPolls]),
        removePoll: (pollId) => update((currentPolls) => currentPolls.filter(poll => poll.id !== pollId)),
        updatePoll: (poll) => update(
            (currentPolls) => currentPolls.map((pollItem) => pollItem.id !== poll.id ? pollItem : poll)
        ),
        updatePollStatus: (pollId, status) => update(
            (currentPolls) => currentPolls.map(
                (pollItem) => pollItem.id === pollId ? Object.assign({}, pollItem, {status}) : pollItem
            )
        ),
    }
}

export const polls = PollsStore();

const LoadingStore = () => {
    const {subscribe, update} = writable(false);

    return {
        subscribe,
        enable: () => update(() => true),
        disable: () => update(() => false),
    }
}

export const isLoading = LoadingStore();
