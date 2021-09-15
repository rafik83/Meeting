import NotificationSubscriber from "../../_Subscriber";
import { isLoading, polls } from "./PollsStore";
import { loadPolls } from "./api";
import { STATUS_HIDDEN, STATUS_PUBLISHED } from "./status";

const managedPollResults = [];

let notificationSubscriber;

export function startMainSubscriber(happeningId, notificationSubscriberKey, notificationProviderUrl, isAdmin = false) {
    const topicPoll = `https://vimeet.events/happening/${happeningId}/webinar/poll`;

    notificationSubscriber = new NotificationSubscriber(notificationProviderUrl)

    notificationSubscriber.addSubscriber(topicPoll, notificationSubscriberKey, (event) => {
        const payload = JSON.parse(event.data);

        if (payload.action === 'new_poll_published') {
            if (payload.poll.hasVotes) {
                // polls must be reloaded for each viewers if some of them have already voted
                isLoading.enable();
                loadPolls();
                isLoading.disable();
            } else {
                if (!polls.hasPoll(payload.poll.id)) {
                    polls.prependPoll(payload.poll);
                }
                if (isAdmin) {
                    polls.updatePollStatus(payload.poll.id, STATUS_PUBLISHED);
                    startPollResultsSubscriber(payload.poll);
                }
            }
        }

        if (payload.action === 'poll_hidden') {
            if (isAdmin) {
                polls.updatePollStatus(payload.pollId, STATUS_HIDDEN);
            } else {
                polls.removePoll(payload.pollId);
            }
        }
    });
}

export function startPollResultsSubscriber(poll) {
    if (
        poll.resultsSubscriptionKey === null
        || managedPollResults.some((id) => id === poll.id)
        || poll.status !== STATUS_PUBLISHED
    ) {
        return;
    }

    notificationSubscriber.addSubscriber(getPollResultsTopic(poll.id), poll.resultsSubscriptionKey, (event) => {
        const payload = JSON.parse(event.data);

        if (payload.action === 'poll_vote_added') {
            polls.updatePoll(payload.poll);
        }
    });

    managedPollResults.push(poll.id);
}

export function stopPollResultsSubscribers() {
    let pollId;
    while (pollId = managedPollResults.pop()) {
        notificationSubscriber.removeSubscriber(getPollResultsTopic(pollId));
    }
}

function getPollResultsTopic(pollId) {
    return `https://vimeet.events/poll/${pollId}/results`;
}
