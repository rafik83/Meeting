import Hls from 'hls.js';

let fatalErrorsCount = 0;

export default class HlsPlayer {
    constructor(videoElement, onReadyCallback) {
        this.videoElement = videoElement;
        this.onReadyCallback = onReadyCallback;
        this.onPlayerReady.bind(this);
        this.onError.bind(this);
    }

    initHlsStreamPlayer(hlsUrl) {
        if (Hls.isSupported()) {
            this.hls = new Hls({
                liveDurationInfinity: true,
                startLevel: -1,
                debug: false,
            });
            this.hls.loadSource(hlsUrl);
            this.hls.attachMedia(this.videoElement);
            this.hls.on(Hls.Events.MANIFEST_PARSED, () => this.onPlayerReady());
            this.hls.on(Hls.Events.ERROR, (event, data) =>
                this.onError(event, data)
            );
        } else if (
            this.videoElement.canPlayType('application/vnd.apple.mpegurl')
        ) {
            // fallback for browsers without hls support
            this.videoElement.src = hlsUrl;
            this.videoElement.addEventListener(
                'loadedmetadata',
                this.onPlayerReady
            );
        }
    }

    updateHlsSource(hlsUrl) {
        if (this.errorRecoveryTimerId) {
            clearTimeout(this.errorRecoveryTimerId);
        }
        if (!this.hls) {
            this.initHlsStreamPlayer(hlsUrl);
        } else if (hlsUrl !== this.hls.url) {
            this.hls.loadSource(hlsUrl);
        }
    }

    isInitialized() {
        return !!this.hls;
    }

    onPlayerReady() {
        this.videoElement.play();
        this.onReadyCallback();
    }

    onError(event, data) {
        if (this.errorRecoveryTimerId) {
            clearTimeout(this.errorRecoveryTimerId);
        }
        switch (data.type) {
            case Hls.ErrorTypes.NETWORK_ERROR:
                console.warn('[HlsPlayer] fatal network error encountered, try to recover');
                this.errorRecoveryTimerId = setTimeout(() => this.hls.startLoad(), 5000);
                break;
            case Hls.ErrorTypes.MEDIA_ERROR:
                console.warn('[HlsPlayer] fatal media error encountered, try to recover');
                this.errorRecoveryTimerId = setTimeout(() => this.hls.recoverMediaError(), 5000);
                break;
            default:
                console.error('[HlsPlayer] fatal non recoverable error #' + fatalErrorsCount, data);
                const currentUrl = this.hls.url;
                this.hls.destroy();
                this.hls = null;
                if (fatalErrorsCount < 3) {
                    this.errorRecoveryTimerId = setTimeout(() => this.initHlsStreamPlayer(currentUrl), 10000);
                    fatalErrorsCount++;
                } else {
                    // failover: refresh browser after 3 errors
                    document.location.reload();
                }
                break;
        }
    }
}
