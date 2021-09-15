class SheetVideo {
    constructor(element) {
        const callbackUrl = element.getAttribute('data-play-callback-url');
        element.addEventListener('play', (event) => {
            fetch(callbackUrl);
        });
    }
}

export default SheetVideo;
