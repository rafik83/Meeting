
function UploadPreview(element)
{
  this.element = element;
  this.target  = document.querySelector(this.element.getAttribute('data-registration-object-type-image-upload'));
  this.element.addEventListener('change', this.readFile.bind(this));
}

UploadPreview.prototype.readFile = function () {
  if (this.element.files && this.element.files[0]) {
    var reader = new FileReader();

    reader.onload = function (event) {
      if (this.target.tagName !== 'IMG') {
        var img = document.createElement('img');

        this.target.parentNode.insertBefore(img, this.target);
        this.target.parentNode.removeChild(this.target);

        img.id = this.target.id;
        img.setAttribute('src', event.target.result);
        this.target = img;
      } else {
        this.target.setAttribute('src', event.target.result);
      }
    }.bind(this);

    reader.readAsDataURL(this.element.files[0]);
  }
};

module.exports = UploadPreview;
