var axios = require('axios');

/**
 * @param {element} element
 * @param {element} targetElement
 * @constructor
 */
function TipPreview (element, targetElement)
{
    this.input         = element;
    this.targetElement = targetElement;

    this.input.addEventListener('change', this.displayPreviewData.bind(this));
}

TipPreview.prototype.displayPreviewData = function ()
{
    this.input.remove(0);

    var selectedIndex = this.input.options.selectedIndex,
        url           = this.input.options[selectedIndex].getAttribute('data-preview-url'),
        $this         = this,
        previewData   = function (data) {

        var previewTitle   = document.createElement('h5'),
            previewContent = document.createElement('p');
            previewTitle.innerHTML   = data.title;
            previewContent.innerHTML = data.content;

        while ($this.targetElement.firstChild) {
            $this.targetElement.removeChild($this.targetElement.firstChild);
        }

        $this.targetElement.appendChild(previewTitle);
        $this.targetElement.appendChild(previewContent);
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

module.exports = TipPreview;
