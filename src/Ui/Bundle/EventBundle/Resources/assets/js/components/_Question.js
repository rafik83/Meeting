'use strict';

import $ from "jquery";
import Modal from "./Modal";

function Question(element) {
    this.questionsContainer = element.querySelector('[data-questions-container]');
    this.questionsList = this.questionsContainer.querySelector('.questions-list');
    this.questionsForm = element.querySelector('[data-questions-form]');
    this.questionsFormContent = this.questionsForm.querySelector('input[name="content"]');
    this.questionsFormAction = this.questionsForm.getAttribute('action');
    this.questionsFormSubmit = this.questionsForm.querySelector('button[type="submit"]');

    this.questionsForm.addEventListener('submit', this.submitQuestion.bind(this));

    this.questionVoteMessage = element.getAttribute('data-question-vote-message');
    this.questionUnvoteMessage = element.getAttribute('data-question-unvote-message');
    this.questionVoteDisabledMessage = element.getAttribute('data-question-vote-disabled-message');

    this.questionCanModerate = element.hasAttribute('data-question-can-moderate');
    this.questionReplyLabel = this.questionsContainer.getAttribute('data-question-reply-label');
    this.questionReplyWriting = this.questionsContainer.getAttribute('data-question-begin-reply-label');

    if (this.questionCanModerate){
        this.questionDeleteConfirmModalElement = element.querySelector('[data-modal-delete-question-message]');
        this.questionDeleteConfirmModal = new Modal();
        this.questionDeleteConfirmModal.init(this.questionDeleteConfirmModalElement);

        this.questionsBeginReplyEndPoint = this.questionsContainer.getAttribute('data-question-begin-reply');
        this.questionsReplyEndPoint = this.questionsContainer.getAttribute('data-question-reply');
        this.questionReplyButtonLabel = this.questionsContainer.getAttribute('data-question-reply-button-label');
        this.questionDeleteReplyConfirmModalElement = element.querySelector('[data-modal-delete-question-reply]');
        this.questionDeleteReplyConfirmModal = new Modal();
        this.questionDeleteReplyConfirmModal.init(this.questionDeleteReplyConfirmModalElement);
    }

    this.questionListeners = [];
    this.questionMessageCount = 0;
}

Question.prototype.initQuestions = function () {
    const href = this.questionsContainer.getAttribute('data-href');
    const voteHref = this.questionsContainer.getAttribute('data-vote-href');
    const urlQuestionDelete = this.questionsContainer.getAttribute('data-question-message-delete');

    const $questionsList = $(this.questionsList);

    $.get(href, function (response) {
        // make shure there no listeners leak
        this.removeQuestionListeners();
        $questionsList.empty();
        let questionMessageCount = 0;
        response.forEach((item) => {
            const rowEl = document.createElement('div');
            rowEl.classList.add('question-row');
            rowEl.id = 'question-' + item.questionId;

            const contentEl = rowEl.appendChild(document.createElement('div'));
            contentEl.classList.add('question-content');

            const divIcon = rowEl.appendChild(document.createElement('div'));
            divIcon.classList.add('question-div-icon', 'pull-right');

            const questionAside = document.createElement('small');
            questionAside.classList.add('question-vote');

            const questionIcon = document.createElement('small');
            questionIcon.classList.add('question-icon');

            const likeBlock = document.createElement('div');
            const voteCount = document.createElement('span');
            questionMessageCount++;

            if (+item.voteCount) {
                voteCount.textContent = item.voteCount;
                voteCount.classList.add('question-vote-count')
            }

            likeBlock.append(voteCount);

            const likeBtn = document.createElement('i');
            likeBtn.classList.add('glyphicon', 'glyphicon-thumbs-up', 'btn', 'btn-xs');
            likeBtn.setAttribute('data-question-id', item.questionId);

            const onLikedClicked = function (event) {
                const payload = {'questionId': event.currentTarget.getAttribute('data-question-id')};
                $.post(voteHref, JSON.stringify(payload), (response) => {
                    if (response.status !== 'ok') {
                        this.showError('Question vote failed');
                    }
                }, 'json');
                event.currentTarget.classList.add('disabled');

                // remove all listeners, they'll be added again on questions update
                this.removeQuestionListeners();
            }.bind(this);

            if (item.canVote) {
                likeBtn.addEventListener('click', onLikedClicked);
                this.questionListeners.push([likeBtn, onLikedClicked]);

                if (item.isLiked) {
                    likeBtn.classList.add('btn-primary', 'disabled');
                } else {
                    likeBtn.classList.add('btn-gray');
                }
                likeBtn.title = item.isLiked ? this.questionUnvoteMessage : this.questionVoteMessage;
            } else {
                likeBtn.classList.add('btn-gray', 'disabled');
                likeBtn.title = this.questionVoteDisabledMessage;
            }
            likeBlock.appendChild(likeBtn);

            questionAside.append(likeBlock);

            const questionCreatedAt = document.createElement('div');
            questionCreatedAt.textContent = item.createdAt;
            questionIcon.appendChild(questionCreatedAt);
            divIcon.appendChild(questionIcon);
            divIcon.appendChild(questionAside);
            contentEl.appendChild(divIcon);
            contentEl.appendChild(document.createTextNode(item.questionContent));

            const authorEl = rowEl.appendChild(document.createElement('div'));
            authorEl.classList.add('question-author');
            const authorNameEl = authorEl.appendChild(document.createElement('span'));
            authorNameEl.classList.add('question-author-name');
            const authorNameTextEl = authorNameEl.appendChild(document.createElement('span'));
            authorNameTextEl.textContent = item.firstName + ' ' + item.lastName;

            if (item.sheetTitle) {
                const authorTitleEl = authorNameEl.appendChild(document.createElement('small'));
                authorTitleEl.textContent = [item.position, item.sheetTitle].filter((item) => !!item).join(', ');
                authorTitleEl.classList.add('question-author-title');
            }

            const avatarEl = authorEl.appendChild(document.createElement('span'));

            if (item.avatar) {
                avatarEl.classList.add('question-author-avatar');
                const imgEl = avatarEl.appendChild(document.createElement('img'));
                imgEl.setAttribute('src', item.avatar);
            }

            $questionsList[0].appendChild(rowEl);

            const payload = { messageId: item.questionId };
            const onConfirmDelete = () => {
                this.showConfirmAnd(this.questionDeleteConfirmModal, () => {
                    $.post(urlQuestionDelete, JSON.stringify(payload), (response) => {
                        if (response.status !== 'ok') {
                            this.showError('Question delete failed');
                        }
                    }).fail((response)=> {
                        this.showError(response.responseJSON ? response.responseJSON.message : response.status);
                    });
                });
            };

            if (item.reply) {
                this.addReply(item.questionId, item.reply, rowEl);
                questionMessageCount++;
            }

            if (this.questionCanModerate) {
                const questionDeleteMessage = document.createElement('i');
                questionDeleteMessage.classList.add('glyphicon', 'glyphicon-trash');
                questionIcon.appendChild(questionDeleteMessage);
                questionDeleteMessage.addEventListener('click', onConfirmDelete.bind(this));

                if (!item.reply) {
                    this.addReplyButton(rowEl, item.questionId);
                }
            }

        });
        this.questionMessageCount = questionMessageCount;
    }.bind(this))
        .fail(function () {
            console.error('Failed to load webinar questions');
        }.bind(this));
}

/**
 * Show confirm dialog and call callback if confirm button is clicked
 */
Question.prototype.showConfirmAnd = function (confirmModal, callback) {
    confirmModal.show();
    const confirmButton = confirmModal.element.querySelector('[data-modal-confirm]');
    /* Delete previous listener */
    const clonedButton = confirmButton.cloneNode(true);
    confirmButton.parentNode.replaceChild(clonedButton, confirmButton);

    clonedButton.addEventListener('click', ()=> {
        callback();
        confirmModal.hide();
    });
}

Question.prototype.addReply = function (questionId, reply, container) {
    const questionReplyContainer = document.createElement('div');
    questionReplyContainer.classList.add('question-reply');

    if (this.questionCanModerate) {
        const questionReplyActions = document.createElement('div');
        questionReplyActions.classList.add('question-reply-actions', 'pull-right');
        const deleteButton = document.createElement('i');
        deleteButton.classList.add('glyphicon', 'glyphicon-trash');
        questionReplyActions.appendChild(deleteButton);
        deleteButton.addEventListener('click', () => this.onDeleteReply(questionId));

        if (reply.canUpdate) {
            const updateButton = document.createElement('i');
            updateButton.classList.add('glyphicon', 'glyphicon-edit');
            questionReplyActions.appendChild(updateButton);
            updateButton.addEventListener('click', () => this.openUpdateReply(questionReplyContainer, questionId, reply.replyContent));
        }

        questionReplyContainer.appendChild(questionReplyActions);
    }

    const questionRepliedAt = document.createElement('small');
    questionRepliedAt.textContent = reply.repliedAt;
    questionReplyContainer.appendChild(questionRepliedAt);

    const questionReplyIcon = document.createElement('i');
    questionReplyIcon.classList.add('icon-Commentaire');
    const questionRepliedBy = document.createElement('div');
    questionRepliedBy.classList.add('replied-by')
    questionRepliedBy.appendChild(questionReplyIcon);
    questionRepliedBy.appendChild(document.createTextNode(this.questionReplyLabel.replace('%name%', reply.repliedBy)));
    questionReplyContainer.appendChild(questionRepliedBy);

    const questionReplyContent = document.createElement('p');
    questionReplyContent.classList.add('reply-content');

    questionReplyContent.appendChild(document.createTextNode(reply.replyContent));
    questionReplyContainer.appendChild(questionReplyContent);
    container.appendChild(questionReplyContainer);
}

Question.prototype.addReplyButton = function (targetElement, questionId) {
    const questionReplyContainer = document.createElement('div');
    questionReplyContainer.classList.add('question-reply', 'text-right');

    const questionReplyButton = document.createElement('button');
    questionReplyButton.classList.add('btn', 'btn-primary', 'btn-xs', 'btn-reply')
    const questionReplyIcon = document.createElement('i');
    questionReplyIcon.classList.add('icon-Commentaire');
    questionReplyButton.appendChild(questionReplyIcon);
    questionReplyButton.appendChild(document.createTextNode(this.questionReplyButtonLabel));

    questionReplyContainer.append(questionReplyButton);

    targetElement.appendChild(questionReplyContainer);
    questionReplyButton.addEventListener('click', () => this.openReply(questionReplyContainer, questionId));
}

Question.prototype.submitQuestion = function (event) {
    event.preventDefault();
    const questionContent = this.questionsFormContent.value;

    if ('' === questionContent) {
        window.setTimeout(() => this.questionsFormSubmit.disabled = false, 100);
        return;
    }

    this.questionsFormContent.value = '';

    $.post(this.questionsFormAction, JSON.stringify({questionContent: questionContent}), (response) => {
        this.questionsFormSubmit.disabled = false;

        if (response.status === 'ok') {
            this.questionsList.scrollTop = 0;

            return;
        }

        this.questionsFormContent.value = questionContent;
        this.showError('Question creation failed');
    })
        .fail(() => {
            this.questionsFormSubmit.disabled = false;
            this.questionsFormContent.value = questionContent;
            this.showError('Question creation failed');
        });
}

Question.prototype.showWritingRepy = function (questionId, author) {
    const questionRow = this.questionsContainer.querySelector('#question-'+questionId);
    if (!questionRow) {
        return;
    }

    const questionReplyContainer = document.createElement('div');
    questionReplyContainer.classList.add('question-reply');
    const writingReply = document.createElement('div');
    writingReply.classList.add('replied-by');
    writingReply.textContent = this.questionReplyWriting.replace('%name%', author);
    questionReplyContainer.appendChild(writingReply);
    questionRow.appendChild(questionReplyContainer);
}

Question.prototype.openReply = function (targetElement, questionId, defaultContent) {
    // clone add question form
    const questionFormSource = this.questionsContainer.querySelector('[data-questions-form]');
    const questionForm = questionFormSource.cloneNode(true);
    questionForm.removeAttribute('data-questions-form');

    const questionFormInput = questionForm.querySelector('input[name="content"]');
    questionFormInput.removeAttribute('placeholder');
    if (defaultContent) {
        questionFormInput.value = defaultContent;
    }

    removeAllChildren(targetElement);
    targetElement.appendChild(questionForm);

    questionFormInput.focus();

    questionForm.addEventListener('submit', (event) => this.submitQuestionReply(event, questionId, questionFormInput.value));

    $.post(this.questionsBeginReplyEndPoint, JSON.stringify({questionId}))
        .fail((response) => {
            this.showError(response.responseJSON ? response.responseJSON.message : response.status);
        });
}

Question.prototype.submitQuestionReply = function (event, questionId, content) {
    event.preventDefault();

    const replyForm = event.currentTarget;
    const questionRow = replyForm.parentNode.parentNode;

    if ('' === content) {
        replyForm.remove();
        this.addReplyButton(questionRow, questionId);

        return;
    }

    replyForm.querySelectorAll('input,button').forEach((node) => node.disabled = true);

    $.post(
        this.questionsReplyEndPoint,
        JSON.stringify({questionId, content}),
        (payload) => {
            if (payload.status === 'ok') {
                replyForm.remove();

                return;
            }

            replyForm.querySelector('input').value = content;
            this.showError('Reply failed');
        })
    .fail((response) => {
        this.showError(response.responseJSON ? response.responseJSON.message : response.status);
    });
}

Question.prototype.onDeleteReply = function (questionId) {
    const payload = {questionId};
    const urlQuestionReplyDelete = this.questionsContainer.getAttribute('data-question-reply-delete');

    this.showConfirmAnd(this.questionDeleteReplyConfirmModal, () => {
        $.post(urlQuestionReplyDelete, JSON.stringify(payload), (response) => {
            if (response.status !== 'ok') {
                this.showError('Reply delete failed');
            }
        }).fail((response)=> {
            this.showError(response.responseJSON ? response.responseJSON.message : response.status);
        });
    });
}

Question.prototype.openUpdateReply = function (questionReplyContainer, questionId, replyContent) {
    this.openReply(questionReplyContainer, questionId, replyContent);
}

Question.prototype.removeQuestionListeners = function () {
    this.questionListeners.forEach((item) => item[0].removeEventListener('click', item[1]));
    this.questionListeners = [];
}

Question.prototype.showError = function (error) {
    alert('There was an error: ' + (error.name ? error.name : error) + (error.message ? (', ' + error.message) : ''));
};

// utility function to remove all children of an DOM element
function removeAllChildren(element) {
    while (element.firstChild) {
        element.removeChild(element.lastChild);
    }
}

export default Question;
