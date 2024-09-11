function clickImage(imageFile) {
	parent.opener.document.getElementById('imageFunc').value = imageFile;
	parent.opener.document.getElementById('imageSrcFunc').src = "image/" + imageFile;
	self.close();
}