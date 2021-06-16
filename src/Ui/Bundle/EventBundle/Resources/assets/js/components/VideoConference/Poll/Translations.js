'use strict';

let translations;

export function trans(key) {
    if (!translations) {
        const rawData = document.getElementById('poll-translations');
        translations = JSON.parse(rawData.innerHTML);
   }

    if (translations.hasOwnProperty(key)) {
        return translations[key];
    } else {
        console.warn(`No translation for key ${key}`);

        return key;
    }
}
