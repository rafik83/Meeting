import { polls } from "./PollsStore";
import { startPollResultsSubscriber } from "./PollNotificationSubscriber";
import { trans } from "./Translations";

let getPollsUrl;
let savePollUrl;
let votePollUrl;
let setPollStatusUrl;

export function setUrls(getPolls, savePoll, votePoll, setPollStatus) {
    getPollsUrl = getPolls;
    savePollUrl = savePoll;
    votePollUrl = votePoll;
    setPollStatusUrl = setPollStatus;
}

export async function loadPolls() {
    const apiResult = await fetchResponse(getPollsUrl);

    polls.setPolls(apiResult);

    apiResult.forEach(poll => startPollResultsSubscriber(poll));
}

export async function savePoll(payload) {
    const apiResult = await fetchResponse(savePollUrl, {method: 'POST', body: JSON.stringify(payload)});

    if (apiResult.status !== 'ok') {
        console.error(`Error while posting poll data to api: ${apiResult.status}`);
        if (apiResult.status) {
            displayError(apiResult.status);
        }
        return;
    }
}

export async function votePoll(pollId, choicesId) {
    const apiResult = await fetchResponse(
        votePollUrl,
        {method: 'POST', body: JSON.stringify({choices: choicesId, pollId})}
    );

    if (apiResult.status !== 'ok') {
        console.error(`Error while voting for poll #${pollId}: ${apiResult.status}`);
        if (apiResult.status) {
            displayError(apiResult.status);
        }
        return;
    }

    return apiResult;
}

export async function setPollStatus(pollId, status) {
    const payload = {id: pollId, status};
    const apiResult = await fetchResponse(setPollStatusUrl, {method: 'PUT', body: JSON.stringify(payload)});
    if (apiResult.status !== 'ok') {
        console.error(`Error while puting poll status to api: ${apiResult.status}`);
        if (apiResult.status) {
            displayError(apiResult.status);
        }
    }
}

async function fetchResponse(url, options = {}) {
    const response = await fetch(url, options);
    if (response.status >= 400) {
        displayError(response.status);
        console.error(`API call error, url: ${url}, response status: ${response.status}`);
    }

    let result = {};
    try {
        result = await response.json();
    } catch(error) {
        console.error(error);
    }

    return result;
}

function displayError(code) {
    alert(trans('apiError').replace('%code%', code));
}
