"use strict";

import { CHROME_EXTENSION_URL, TokboxInstance } from "../TokboxInstance";
import Publisher, { STREAM_TYPE_SCREENSHARE } from "../Publisher";
import DesktopNotification from "../DesktopNotification";

export default class ScreenSharing {
    constructor(element, getSession, layoutContainer, getCameraElement) {
        this.element = element;
        this.getSession = getSession;
        this.layoutContainer = layoutContainer;
        this.getCameraElement = getCameraElement;

        this.notCompatibleBrowserMessage = element.getAttribute("data-not-compatible-browser-message");
        this.installScreenSharingExtensionMessage = element.getAttribute(
            "data-install-screensharing-extension-message"
        );
        this.mediaShareScreenShareStatusMessage = element.getAttribute("data-media-screenShareStatus-message");
        this.streamName = element.getAttribute("data-current-user-id");

        this.desktopNotificationTitle = element.getAttribute("data-desktop-notification-title");
        this.desktopNotificationBody = element.getAttribute("data-desktop-notification-body");
        this.desktopNotification = new DesktopNotification(this.desktopNotificationTitle, this.desktopNotificationBody);

        this.sharingActive = false;
        this.pipVideoElement = null;
    }

    startSharing() {
        TokboxInstance.checkScreenSharingCapability(async (response) => {
            if (!response.supported || response.extensionRegistered === false) {
                alert(this.notCompatibleBrowserMessage);
                return;
            }

            if (response.extensionRegistered && response.extensionInstalled === false) {
                this.installChromeExtension();
                return;
            }

            const session = this.getSession();
            if (!session) {
                throw new Error("Video session not available");
            }

            this.publisherScreen = new Publisher(null);
            const publisherScreen = this.publisherScreen.create({
                videoSource: STREAM_TYPE_SCREENSHARE,
                publishAudio: true,
                name: this.streamName,
                insertDefaultUI: false,
                maxResolution: {
                    width: 1280,
                    height: 720,
                },
            });

            // create placeholder for screen sharing (avoid video larsen)
            this.screenElement = document.createElement("div");
            this.screenElement.classList.add("screen-share-in-progress");

            const placeHolderEndSharingButton = document.createElement("button");
            const endSharingButton = this.element.querySelector("#media-stop-sharing");
            placeHolderEndSharingButton.textContent = endSharingButton.textContent;
            placeHolderEndSharingButton.classList.add("btn");
            placeHolderEndSharingButton.classList.add("btn-primary");
            placeHolderEndSharingButton.addEventListener("click", this.stopSharing.bind(this));

            const screenCenteredElement = document.createElement("div");
            screenCenteredElement.textContent = this.mediaShareScreenShareStatusMessage;
            screenCenteredElement.appendChild(document.createElement("hr"));
            screenCenteredElement.appendChild(placeHolderEndSharingButton);

            this.screenElement.appendChild(screenCenteredElement);
            this.layoutContainer.appendChild(this.screenElement);
            session.publish(publisherScreen, this.handlePublishMediaSharing.bind(this));

            const event = new CustomEvent("maximize", {
                detail: {
                    target: this.screenElement,
                },
            });
            this.layoutContainer.dispatchEvent(event);

            const videoContainer = this.getCameraElement();
            const videoElement = videoContainer && videoContainer.querySelector("video.OT_video-element");
            if (videoElement && document.pictureInPictureEnabled && !videoElement.disablePictureInPicture) {
                try {
                    await videoElement.requestPictureInPicture();
                    this.pipVideoElement = videoElement;
                    window.addEventListener('blur', this.onWindowBlurred.bind(this));
                } catch (err) {
                    console.error(err);
                }
            } else {
                this.pipVideoElement = null;
            }

            publisherScreen.on("streamCreated", (event) => {
                this.onSharingStartedCallback(event.stream, STREAM_TYPE_SCREENSHARE);
                this.currentStream = event.stream;
                if (!this.pipVideoElement) {
                    this.desktopNotification.showPresent();
                } else {
                    window.addEventListener('click', this.onWindowFocused.bind(this));
                }
            });
            publisherScreen.on("streamDestroyed", () => {
                this.currentStream = null;
            });

            publisherScreen.on("mediaStopped", () => {
                if (this.sharingActive) {
                    window.focus();
                    this.stopSharing();
                }
                this.currentStream = null;
            });
        });
    }

    onWindowFocused() {
        if (document.pictureInPictureElement) {
            document.exitPictureInPicture();
        }
    }

    onWindowBlurred() {
        if (!document.pictureInPictureElement && this.pipVideoElement) {
            this.pipVideoElement.requestPictureInPicture();
        }
    }

    /**
     * Callback after screen sharing started
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

    /**
     * Handle stop screen sharing
     */
    async stopSharing() {
        if (this.screenElement) {
            this.screenElement.remove();
            this.screenElement = null;
        }

        if (document.pictureInPictureElement) {
            await document.exitPictureInPicture();
        }
        if (this.pipVideoElement) {
            this.onWindowFocused();
            window.removeEventListener('click', this.onWindowFocused);
            window.removeEventListener('blur', this.onWindowBlurred);
            this.pipVideoElement = null;
        }

        if (this.desktopNotification.isOpened()) {
            this.desktopNotification.closePresent();
        }

        this.onSharingStoppedCallback(this.currentStream, STREAM_TYPE_SCREENSHARE);

        if (this.publisherScreen) {
            this.publisherScreen.destroy();
        }

        this.sharingActive = false;
    }

    installChromeExtension() {
        alert(this.installScreenSharingExtensionMessage);
        window.open(CHROME_EXTENSION_URL, "_blank");
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
