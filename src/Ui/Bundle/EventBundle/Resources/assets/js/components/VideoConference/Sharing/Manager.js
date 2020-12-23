"use strict";

import $ from "jquery";
import "bootstrap/js/tooltip";
import "bootstrap/js/popover"; // popover require tooltip
import ScreenSharing from "./ScreenSharing";
import VideoSharing from "./VideoSharing";

/**
 * Screen / Video sharing manager (speaker only)
 */
export default class Manager {
    constructor(element, getSession, layoutContainer, getCameraElement) {
        this.element = element;
        this.getSession = getSession;
        this.layoutContainer = layoutContainer;
        this.getCameraElement = getCameraElement;

        this.notCompatibleBrowserMessage = element.getAttribute("data-not-compatible-browser-message");
        this.installScreenSharingExtensionMessage = element.getAttribute(
            "data-install-screensharing-extension-message"
        );
        this.streamName = element.getAttribute("data-current-user-id");
        this.thereIsAlreadyAScreenShareInProgressMessage = element.getAttribute(
            "data-screen-share-already-in-progress-message"
        );
        this.mediaShareButtonScreenShareMessage = element.getAttribute("data-media-share-button-screenshare-message");
        this.mediaShareButtonVideoShareMessage = element.getAttribute("data-media-share-button-videoshare-message");

        this.$sharePopover = $("#media-start-sharing", this.element);
        this.mediaStartSharingButton = this.$sharePopover.get(0);

        this.endSharingButton = element.querySelector("#media-stop-sharing");
        this.endSharingButton.addEventListener("click", this.stopSharing.bind(this));

        this.screenSharing = new ScreenSharing(element, getSession, layoutContainer, getCameraElement);
        this.videoSharing = new VideoSharing(element, getSession, layoutContainer, getCameraElement);

        this.mediaSharing = null;
    }

    init() {
        // use event listener instead of trigger:click to avoid having to click twice when mediaStartSharingButton switch back from hidden to shown
        this.mediaStartSharingButton.addEventListener("click", () => this.$sharePopover.popover("toggle"));
        this.mediaStartSharingButton.classList.remove("hide");

        this.$sharePopover.popover({
            animation: false,
            html: true,
            placement: "top",
            trigger: "manual",
            content: () => {
                return `<div class="text-center">
                    <span class="btn btn-share-screen">${this.mediaShareButtonScreenShareMessage}</span><br />
                    <span class="btn btn-share-video">${this.mediaShareButtonVideoShareMessage}</span>
                  </div>`;
            },
        });

        this.$sharePopover.on("shown.bs.popover", () => {
            const shareScreenButton = this.element.querySelector(".btn-share-screen");
            shareScreenButton.addEventListener("click", () => this.startSharing(this.screenSharing));

            const shareVideoButton = this.element.querySelector(".btn-share-video");
            shareVideoButton.addEventListener("click", () => this.startSharing(this.videoSharing));
        });
    }

    startSharing(mediaSharing) {
        if (null === this.getSession()) {
            alert("You cannot start screensharing outside of a session");
            return;
        }

        if (this.mediaSharing && this.mediaSharing.sharingActive) {
            alert(this.thereIsAlreadyAScreenShareInProgressMessage);
            return;
        }

        this.$sharePopover.popover("hide");

        this.endSharingButton.classList.remove("hide");
        this.mediaStartSharingButton.classList.add("hide");

        this.mediaSharing = mediaSharing;
        this.mediaSharing.onSharingStarted(this.onSharingStartedCallback);
        this.mediaSharing.onSharingStopped(this.onSharingStoppedCallback);
        this.mediaSharing.onSharingError(this.onSharingErrorCallback);
        mediaSharing.startSharing();
    }

    stopSharing() {
        this.mediaSharing.stopSharing();
    }

    onSharingStarted(callback) {
        this.onSharingStartedCallback = callback;
    }

    onSharingStopped(callback) {
        this.onSharingStoppedCallback = (stream, type) => {
            this.mediaStartSharingButton.classList.remove("hide");
            this.endSharingButton.classList.add("hide");
            this.mediaSharing = null;
            this.layoutContainer.dispatchEvent(new Event("maximizeAll"));

            callback(stream, type);
        };
    }

    onSharingError(callback) {
        this.onSharingErrorCallback = callback;
    }
}
