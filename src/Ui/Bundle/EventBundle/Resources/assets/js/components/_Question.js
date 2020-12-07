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

    this.chatUnVoteMessage = element.getAttribute('data-chat-unvote-message');
    this.chatVoteDisabledMessage = element.getAttribute('data-chat-vote-disabled-message');

    this.questionCanDelete = element.hasAttribute('data-question-can-delete');

    if (this.questionCanDelete){
        this.questionDeleteConfirmModalElement = element.querySelector('[data-modal-delete-question-message]');
        this.questionButtonConfirm = this.questionDeleteConfirmModalElement.querySelector('[data-modal-confirm]')
    }

    if (this.questionDeleteConfirmModalElement){
        this.questionDeleteConfirmModal = new Modal();
        this.questionDeleteConfirmModal.init(this.questionDeleteConfirmModalElement);
    }

    this.questionListeners = [];
    this.questionMessageCount = 0;
}

Question.prototype.initQuestions = function () {
    const href = this.questionsContainer.getAttribute('data-href');
    const voteHref = this.questionsContainer.getAttribute('data-vote-href');
    const questionMessageDelete = this.questionsContainer.getAttribute('data-question-message-delete');

    const $questionsList = $(this.questionsList);

    $.get(href, function (response) {
        // make shure there no listeners leak
        this.removeQuestionListeners();
        $questionsList.empty();
        let questionMessageCount = 0;
        response.forEach((item) => {
            const rowEl = document.createElement('div');
            rowEl.classList.add('question-row');

            const contentEl = rowEl.appendChild(document.createElement('div'));
            contentEl.classList.add('question-content');

            const divIcon = rowEl.appendChild(document.createElement('div'));
            divIcon.classList.add('question-div-icon');

            const questionAside = document.createElement('small');
            questionAside.classList.add('pull-right', 'question-aside', 'question-vote');

            const questionIcon = document.createElement('small');
            questionIcon.classList.add('pull-right', 'question-aside', 'question-icon');

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


            const onConfirmDelete = function() {
                this.questionDeleteConfirmModal.show();
                /* Delete previous listener */
                const clonedButton = this.questionButtonConfirm.cloneNode(true);
                this.questionButtonConfirm.parentNode.replaceChild(clonedButton, this.questionButtonConfirm);
                this.questionButtonConfirm = clonedButton;

                const payload = { messageId: item.questionId };
                this.questionButtonConfirm.addEventListener('click', ()=> {
                    $.post(questionMessageDelete, JSON.stringify(payload), (response) => {
                        if (response.status !== 'ok') {
                            this.showError('Message delete failed');
                        } /* Todo error 403 404 */
                    }, 'json');
                    this.questionDeleteConfirmModal.hide();
                });
            }.bind(this);

            if (this.questionCanDelete) {
                const questionDeleteMessage = document.createElement('i');
                questionDeleteMessage.classList.add('glyphicon', 'glyphicon-trash');
                questionIcon.appendChild(questionDeleteMessage);
                questionDeleteMessage.addEventListener('click', onConfirmDelete);
            }

        });
        this.questionMessageCount = questionMessageCount;
    }.bind(this))
        .fail(function () {
            console.error('Failed to load webinar questions');
        }.bind(this));
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

Question.prototype.sendUpdateQuestionsSignal = function () {
    this.session.signal({
            type: 'QuestionsUpdate'
        },
        (error) => {
            if (error) {
                console.error('QuestionsUpdate signal error', error);
            }
        });
}

Question.prototype.removeQuestionListeners = function () {
    this.questionListeners.forEach((item) => item[0].removeEventListener('click', item[1]));
    this.questionListeners = [];
}

Question.prototype.showError = function (error) {
    alert('There was an error: ' + (error.name ? error.name : error) + (error.message ? (', ' + error.message) : ''));
};

export default Question;
