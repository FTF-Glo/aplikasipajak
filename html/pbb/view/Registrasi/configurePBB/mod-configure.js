
extArray = new Array(".gif", ".jpg", ".png");
function LimitAttach(form, file) {
	/*allowSubmit = false;
	if (!file) return;
	while (file.indexOf("\\") != -1)
	file = file.slice(file.indexOf("\\") + 1);
	ext = file.slice(file.indexOf(".")).toLowerCase();
	for (var i = 0; i < extArray.length; i++) {
		if (extArray[i] == ext) { 
			allowSubmit = true; break; 
		}
	}
	if (allowSubmit) return true;
	else {
	alert("Please only upload files that end in types:  " 
	+ (extArray.join("  ")) + "\nPlease select a new "
	+ "file to upload and submit again.");
	return false;
	}*/
	return false;

}

function validateForm(){
	allowSubmit = false;
	var file = document.getElementById('logo_file').value;
	var img = document.getElementById("img-logo").value;
	console.log(img);
    if (!file) {
		//alert ("File gambar kosong !") ; 
		if (!img) return false;
		else return true;
	}
	while (file.indexOf("\\") != -1)
	file = file.slice(file.indexOf("\\") + 1);
	var ext = file.slice(file.indexOf(".")).toLowerCase();
	for (var i = 0; i < extArray.length; i++) {
		if (extArray[i] == ext) { 
			//allowSubmit = true;
			return true; 
		}
	}
	//if (allowSubmit) return true;
	//else {
	alert("File yang bisa di upload adalah file yang ber-extention:  " 
	+ (extArray.join("  ")) + "\nSilahkan pilih kembali "
	+ "file untuk di upload !.");
	return false;
	//}
}

