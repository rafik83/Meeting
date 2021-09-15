"use strict";

import Publisher, { STREAM_TYPE_CUSTOM } from "../Publisher";

/**
 * Share a video from url
 * ex: https://opentok.github.io/opentok-web-samples/Publish-Video/video/BigBuckBunny_320x180.mp4
 * Url must have cors allowed for domain
 */
export default class VideoSharing {
    constructor(element, getSession, layoutContainer) {
        this.element = element;
        this.getSession = getSession;
        this.layoutContainer = layoutContainer;

        this.notCompatibleBrowserMessage = element.getAttribute("data-not-compatible-browser-message");
        this.mediaShareUrlVideoMessage = element.getAttribute("data-media-share-url-video-message");
        this.mediaShareUrlVideoSecurityErrorMessage = element.getAttribute(
            "data-media-share-url-video-security-error-message"
        );
        this.mediaShareUrlVideoLoadingErrorMessage = element.getAttribute(
            "data-media-share-url-video-loading-error-message"
        );
    }

    startSharing() {
        const videoElement = document.createElement("video");
        videoElement.setAttribute("crossOrigin", "anonymous");
        videoElement.setAttribute("controls", "");
        videoElement.setAttribute("preload", "auto");
        videoElement.setAttribute("controlslist", "disablePictureInPicture nodownload nofullscreen noremoteplayback");
        videoElement.setAttribute("disablePictureInPicture", "");
        this.layoutContainer.appendChild(videoElement);
        const event = new CustomEvent("maximize", {
            detail: {
                target: videoElement,
            },
        });
        this.layoutContainer.dispatchEvent(event);

        this.shareVideoElement = videoElement;

        if (!videoElement.captureStream) {
            alert(this.notCompatibleBrowserMessage);
            this.stopSharing();
            return;
        }

        const url = this.askUrlVideo();

        if (!url) {
            this.stopSharing();
            return;
        }

        videoElement.addEventListener(
            "error",
            () => {
                this.stopSharing();
                alert(this.mediaShareUrlVideoLoadingErrorMessage);
            },
            true
        );

        videoElement.src = url;
        videoElement.play();

        const stream = videoElement.mozCaptureStream ? videoElement.mozCaptureStream() : videoElement.captureStream();

        let publisher;

        const publishVideo = () => {
            const videoTracks = stream.getVideoTracks();
            const audioTracks = stream.getAudioTracks();

            if (publisher || !videoTracks.length) {
                return;
            }
            stream.removeEventListener("addtrack", publishVideo);

            const session = this.getSession();
            if (!session) {
                throw new Error("Video session not available");
            }

            this.publisherScreen = new Publisher(null);
            publisher = this.publisherScreen.create({
                videoSource: videoTracks[0],
                audioSource: audioTracks[0],
                fitMode: "contain",
                insertDefaultUI: false,
            });

            session.publish(publisher, this.handlePublishMediaSharing.bind(this));

            publisher.on("streamCreated", (event) => {
                this.currentStream = event.stream;
                this.onSharingStartedCallback(event.stream, STREAM_TYPE_CUSTOM);
            });

            publisher.on("streamDestroyed", (event) => {
                this.currentStream = null;
            });
        };

        stream.addEventListener("addtrack", publishVideo);
        publishVideo();
    }

    askUrlVideo(previousUrl) {
        const url = window.prompt(this.mediaShareUrlVideoMessage, previousUrl);

        if (!url) {
            this.stopSharing();
            return;
        }

        if ("https://" !== url.substr(0, 8)) {
            alert(this.mediaShareUrlVideoSecurityErrorMessage);

            return this.askUrlVideo(url);
        }

        return url;
    }

    /**
     * Callback after screensharing started
     *
     * @param {Object} error
     */
    handlePublishMediaSharing(error) {
        if (error) {
            console.error(error);
            this.stopSharing();
            this.onSharingErrorCallback(error);

            return;
        }

        this.sharingActive = true;
    }

    stopSharing() {
        if (this.shareVideoElement) {
            this.shareVideoElement.remove();
            this.shareVideoElement = null;
        }

        this.onSharingStoppedCallback(this.currentStream, STREAM_TYPE_CUSTOM);

        if (this.publisherScreen) {
            this.publisherScreen.destroy();
        }

        this.sharingActive = false;
    }

    onSharingStarted(callback) {
        this.onSharingStartedCallback = callback;
    }

    onSharingStopped(callback) {
        this.onSharingStoppedCallback = callback;
    }

    onSharingError(callback) {
        this.onSharingErrorCallback = callback;
    }
}
