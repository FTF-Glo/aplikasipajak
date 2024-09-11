function disableSelection(target) {
	if (typeof target.onselectstart != "undefined") {
		// IE route
		if (!target.onselectstart) {
			target.onselectstart = function() {
				return false;
			}
		}
	} else if (typeof target.style.MozUserSelect != "undefined") {
		// Firefox route
		target.style.MozUserSelect = "none";
	} else {
		// All other route (ie: Opera)
		if (!target.onmousedown) {
			target.onmousedown = function() {
				return false;
			}
		}
	}
}

function isBodyExist() {
	var body = document.getElementsByTagName("*");
	return (body != null);
}

function applyDefaultEnable() {
	var allElement = document.getElementsByTagName("*");
	var length = allElement.length;
	var startBody = false;

	if (!isBodyExist()) {
		startBody = true;
	}

	for (var i = 0; i < length; i++) {
		var element = allElement[i];
		if (element == undefined) {
			continue;
		}

		var disable = element.getAttribute('disableSelection');
		
		var name = element.tagName.toLowerCase();
		if (startBody) {
			if (disable == 'true') {
				disableSelection(element);
			}
		}

		if (name == 'body') {
			startBody = true;
		}
	}
}

function applyDefaultDisable() {
	var allElement = document.getElementsByTagName("*");
	var length = allElement.length;
	var startBody = false;

	if (!isBodyExist()) {
		startBody = true;
	}

	for (var i = 0; i < length; i++) {
		var element = allElement[i];
		if (element == undefined) {
			continue;
		}

		var disable = element.getAttribute('disableSelection');
		
		var name = element.tagName.toLowerCase();
		if (startBody) {
			if (disable == 'true' || (disable == null && element.children.length == 0)) {
				// alert("disable = " + name + " (id = " + element.id + ")");
				disableSelection(element);
			} else {
				// alert(name + " (id = " + element.id + ")");
			}
		}

		if (name == 'body') {
			startBody = true;
		}
	}
}

/**************

Function:
* applyDefaultDisable()
--> apply ke semua element, dengan default DISABLED selection
* applyDefaultEnable()
--> apply ke semua element, dengan default ENABLED selection

Atribut tag:
* disableSelection='true'
--> apply DISABLED selection ke tag tersebut
* disableSelection='false'
--> apply ENABLED selection ke tag tersebut

Contoh:
<body onLoad='applyDefaultDisable()'>
	<div>disabled text</div>
	<p>still disabled text</p>
	<div disableSelection='true'>disabled text from attribute</div>
	<div disableSelection='false'>enabled text from attribute</div>
</body>

**************/
