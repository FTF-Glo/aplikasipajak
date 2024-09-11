function redirect(action, formId) {
	var form = document.getElementById(formId);
	if (form != null) {
		form.action = action;
		form.submit()
	}
}
var winDetail = null;
function showDetailPpid(area, module, id) {
	var url = Base64.encode("a=" + area + "&m=" + module + "&i=" + id);
	if (!winDetail) {
		winDetail = window.open("view/showDetailPpid.php?param=" + url, "Detail PPID", "toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=1, resizable=1, width=300, height=500")
	} else if (winDetail.closed) {
		winDetail = window.open("view/showDetailPpid.php?param=" + url, "Detail PPID", "toolbar=0, location=0, directories=0, status=0, menubar=0, scrollbars=1, resizable=1, width=300, height=500")
	} else {
		winDetail.focus()
	}
}
function cekConfirmPassword(id1, id2) {
	var pwd = document.getElementById(id1).value;
	var pwd2 = document.getElementById(id2).value;
	if (pwd == pwd2) {
		return true
	} else {
		alert('Wrong confirmed password, please re-entry.');
		return false
	}
}
var nRole = 0;
function removeDiv() {
	var divId = 'div' + this.id.substring(3);
	var div = document.getElementById(divId);
	var tableRole = document.getElementById('divRole');
	tableRole.removeChild(div)
}
function addNewRole(selectRole) {
	var div = document.createElement('div');
	div.setAttribute('id', 'div' + nRole);
	div.innerHTML = 'Role : &nbsp;&nbsp; ' + selectRole + ' &nbsp;&nbsp;&nbsp;&nbsp; ';
	var btnRemove = document.createElement('input');
	btnRemove.setAttribute('id', 'btn' + nRole);
	btnRemove.setAttribute('type', 'button');
	btnRemove.setAttribute('value', '-');
	addEvent(btnRemove, 'click', removeDiv);
	div.appendChild(btnRemove);
	var tableRole = document.getElementById('divRole');
	tableRole.appendChild(div);
	nRole++
}
var _SERVERTIME = new Date();
var _INTERVALID = null;
function parseYMDHMSDate(str) {
	var d = new Date();
	if (str) {
		arr = str.split(" ");
		arrD = arr[0].split("-");
		arrT = arr[1].split(":");
		d.setFullYear(parseInt(arrD[0]));
		d.setMonth(parseInt(arrD[1]) - 1);
		d.setDate(parseInt(arrD[2]));
		d.setHours(parseInt(arrT[0]));
		d.setMinutes(parseInt(arrT[1]));
		d.setSeconds(parseInt(arrT[2]))
	}
	return d
}
function getIndonesianDay(d) {
	switch (d) {
	case 0:
		return "Minggu";
		break;
	case 1:
		return "Senin";
		break;
	case 2:
		return "Selasa";
		break;
	case 3:
		return "Rabu";
		break;
	case 4:
		return "Kamis";
		break;
	case 5:
		return "Jumat";
		break;
	case 6:
		return "Sabtu";
		break;
	default:
		return ""
	}
}
function getIndonesianMonth(m) {
	switch (m) {
	case 0:
		return "Januari";
		break;
	case 1:
		return "Februari";
		break;
	case 2:
		return "Maret";
		break;
	case 3:
		return "April";
		break;
	case 4:
		return "Mei";
		break;
	case 5:
		return "Juni";
		break;
	case 6:
		return "Juli";
		break;
	case 7:
		return "Agustus";
		break;
	case 8:
		return "September";
		break;
	case 9:
		return "Oktober";
		break;
	case 10:
		return "November";
		break;
	case 11:
		return "Desember";
		break;
	default:
		return ""
	}
}
function TickClock() {
	var d = new Date();
	d.setTime(_SERVERTIME);
	_SERVERTIME += 1000;
	var span = document.getElementById('vpos-clock');
	if (span) {
		dt = d.getDate();
		h = d.getHours();
		m = d.getMinutes();
		s = d.getSeconds();
		dt = dt.toString().length == 1 ? "0" + dt.toString() : dt.toString();
		h = h.toString().length == 1 ? "0" + h.toString() : h.toString();
		m = m.toString().length == 1 ? "0" + m.toString() : m.toString();
		s = s.toString().length == 1 ? "0" + s.toString() : s.toString();
		span.innerHTML = getIndonesianDay(d.getDay()) + "," + dt + " " + getIndonesianMonth(d.getMonth()) + " " + d.getFullYear() + " " + h + ":" + m + ":" + s
	}
}
function _getServerTime() {
	return _SERVERTIME.getTime()
}
function DisplayClock() {
	var span = Ext.get('vpos-clock');
	if (span) {
		_SERVERTIME = Date.parse(span.dom.innerHTML)
	}
	span = null;
	_INTERVALID = window.setInterval('TickClock()', 1000)
}
function isNumberKey(evt) {
	var charCode = (evt.which) ? evt.which: event.keyCode;
	if (charCode > 31 && (charCode < 48 || charCode > 57)) {
		return false
	}
	return true
}
function isAlphaNumberKey(evt) {
	var charCode = (evt.which) ? evt.which: event.keyCode;
	if (charCode <= 31 || (charCode >= 48 && charCode <= 57) || (charCode >= 65 && charCode <= 90) || (charCode >= 97 && charCode <= 122)) {
		return true
	}
	return false
}
function uppercase(evt) {
	var charCode = (evt.which) ? evt.which: event.keyCode;
	if ((charCode > 0x60) && (charCode < 0x7B)) {
		evt.which = charCode - 0x20;
		event.keyCode = charCode - 0x20
	}
}