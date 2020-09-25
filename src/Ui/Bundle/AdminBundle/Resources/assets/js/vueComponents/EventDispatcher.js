import Vue from 'vue';

function EventDispatcher(vueInstance) {
    this.vue = vueInstance;
}

/**
 * @param {string} event
 * @param {Object} parameters
 */
EventDispatcher.prototype.dispatch = function (event, parameters) {
    var params = parameters || {};

    this.vue.$emit(event, params);
};

/**
 * @param {string} event
 * @param {function} callback
 */
EventDispatcher.prototype.listen = function (event, callback) {
    this.vue.$on(event, callback);
};

export default new EventDispatcher(new Vue());
