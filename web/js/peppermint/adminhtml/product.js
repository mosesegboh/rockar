/**
 * @category     Peppermint
 * @package      Peppermint_Adminhtml
 * @author       Razvan Zofota <razvan.zofota@rockar.com>
 * @copyright    Copyright (c) 2019 Rockar, Ltd (http://rockar.com)
 */
Product.Gallery.addMethods({
  loadImage: function(file) {
    var image = this.getImageByFile(file);
    var fileElement = this.getFileElement(file, "cell-image img");
    fileElement.src = image.file;
    fileElement.show();
    this.getFileElement(file, "cell-image .place-holder").hide();
  }
});
