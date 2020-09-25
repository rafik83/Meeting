import axios from 'axios';

/**
 * @param {element} element
 * @param {element} targetElement
 * @param {element} secondTargetElement
 * @constructor
 */
function TipPreview (element, targetElement, secondTargetElement)
{
    this.input               = element;
    this.targetElement       = targetElement;
    this.secondTargetElement = secondTargetElement;

    this.input.addEventListener('change', this.displayPreviewData.bind(this));
    window.addEventListener('load', this.displayPreviewData.bind(this));
}

TipPreview.prototype.displayPreviewData = function ()
{
    var selectedIndex = this.input.options.selectedIndex,
        url           = this.input.options[selectedIndex].getAttribute('data-preview-url'),
        $this         = this,
        previewData   = function (data) {

            var previewTitle   = document.createElement('h5'),
                previewContent = document.createElement('p'),
                previewPages   = document.createElement('ul');

            previewTitle.innerHTML   = data.title;
            previewContent.innerHTML = data.content;

            var pages = '';

            for (var i = 0; i < data.pages.length; i++) {
                pages += '<li>' + data.pages[i] + '</li>';
            }

            previewPages.innerHTML = pages;

            while ($this.targetElement.firstChild) {
                $this.targetElement.removeChild($this.targetElement.firstChild);
            }

            while ($this.secondTargetElement.firstChild) {
                $this.secondTargetElement.removeChild($this.secondTargetElement.firstChild);
            }

            $this.targetElement.appendChild(previewTitle);
            $this.targetElement.appendChild(previewContent);
            $this.secondTargetElement.appendChild(previewPages);
        };

    axios
        .get(url)
        .then( function (response) {
            previewData(response.data);
        })
        .catch( function (error) {
            alert(error);
        });
};

export default TipPreview;
