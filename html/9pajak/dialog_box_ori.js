// global variables //
var TIMER = 5;
var SPEED = 10;
var WRAPPER = 'content';

// calculate the current window width //
function pageWidth() {
  return window.innerWidth ? window.innerWidth : document.documentElement && document.documentElement.clientWidth ? document.documentElement.clientWidth : document.body ? document.body.clientWidth : null;
}

// calculate the current window height //
function pageHeight() {
  var w=window;
  var d=document;
  // if (w.innerHeight) {
	// return w.innerHeight;
  // } else if (d.documentElement && d.documentElement.clientHeight) {
	// return d.documentElement.clientHeight;
  // } else if (d.body) {
	// return d.body.clientHeight;
  // } else {
	// return null;
  // }
  
  return w.innerHeight ? w.innerHeight : d.documentElement && d.documentElement.clientHeight ? d.documentElement.clientHeight : d.body ? d.body.clientHeight : null;
}

// calculate the current window vertical offset //
function topPosition() {
  var w=window;
  var d=document;
  return typeof w.pageYOffset != 'undefined' ? w.pageYOffset : d.documentElement && d.documentElement.scrollTop ? d.documentElement.scrollTop : d.body.scrollTop ? d.body.scrollTop : 0;
}

// calculate the position starting at the left of the window //
function leftPosition() {
  var w=window;
  var d=document;
  return typeof w.pageXOffset != 'undefined' ? w.pageXOffset : d.documentElement && d.documentElement.scrollLeft ? d.documentElement.scrollLeft : d.body.scrollLeft ? d.body.scrollLeft : 0;
}

// hide the dialog box //
function hideDialog() {
  var dialog = document.getElementById('dialog');
  clearInterval(dialog.timer);
  dialog.timer = setInterval("fadeDialog(0)", TIMER);
}

// fade-in the dialog box //
function fadeDialog(flag) {
  if(flag === null) {
    flag = 1;
  }
  var dialog = document.getElementById('dialog');
  var value;
  if(flag == 1) {
    value = dialog.alpha + SPEED;
  } else {
    value = dialog.alpha - SPEED;
  }
  dialog.alpha = value;
  dialog.style.opacity = (value / 100);
  dialog.style.filter = 'alpha(opacity=' + value + ')';
  if(value >= 99) {
    clearInterval(dialog.timer);
    dialog.timer = null;
  } else if(value <= 1) {
    dialog.style.visibility = "hidden";
    document.getElementById('dialog-mask').style.visibility = "hidden";
    clearInterval(dialog.timer);
  }
}

// build/show the dialog box, populate the data and call the fadeDialog function //
// function showDialog(title,message,type,autohide,hideclose) {
  // if(!type) {
    // type = 'error';
  // }
  // var dialog;
  // var dialogheader;
  // var dialogclose;
  // var dialogtitle;
  // var dialogcontent;
  // var dialogmask;
  // var w=window;
  // var d=document;
  // if(!d.getElementById('dialog')) {
    // dialog = d.createElement('div');
    // dialog.id = 'dialog';
    // dialogheader = d.createElement('div');
    // dialogheader.id = 'dialog-header';
    // dialogtitle = d.createElement('div');
    // dialogtitle.id = 'dialog-title';
    // dialogclose = d.createElement('div');
    // dialogclose.id = 'dialog-close';
    // dialogcontent = d.createElement('div');
    // dialogcontent.id = 'dialog-content';
    // dialogmask = d.createElement('div');
    // dialogmask.id = 'dialog-mask';
    // d.body.appendChild(dialogmask);
    // d.body.appendChild(dialog);
    // dialog.appendChild(dialogheader);
    // dialogheader.appendChild(dialogtitle);
    // dialogheader.appendChild(dialogclose);
    // dialog.appendChild(dialogcontent);
    // dialogclose.setAttribute('onclick','hideDialog()');
    // dialogclose.onclick = hideDialog;
  // } else {
    // dialog = d.getElementById('dialog');
    // dialogheader = d.getElementById('dialog-header');
    // dialogtitle = d.getElementById('dialog-title');
    // dialogclose = d.getElementById('dialog-close');
    // dialogcontent = d.getElementById('dialog-content');
    // dialogmask = d.getElementById('dialog-mask');
    // dialogmask.style.visibility = "visible";
    // dialog.style.visibility = "visible";
  // }
  // dialog.style.opacity = .00;
  // dialog.style.filter = 'alpha(opacity=0)';
  // dialog.alpha = 0;
  // var width = pageWidth();
  // var height = pageHeight();
  // var left = leftPosition();
  // var top = topPosition();
  // var dialogwidth = dialog.offsetWidth;
  // var dialogheight = dialog.offsetHeight;
  // var topposition = top + (height / 3) - (dialogheight / 2);
  // var leftposition = left + (width / 2) - (dialogwidth / 2);
  // dialog.style.top = topposition + "px";
  // dialog.style.left = leftposition + "px";
  // dialogheader.className = type + "header";
  // dialogtitle.innerHTML = title;
  // dialogcontent.className = type;
  // dialogcontent.innerHTML = message;
  // var content = document.getElementById(WRAPPER);
  // dialogmask.style.height = content.offsetHeight + 'px';
  // dialog.timer = setInterval("fadeDialog(1)", TIMER);
  // if(autohide) {
    // dialogclose.style.visibility = "hidden";
    // window.setTimeout("hideDialog()", (autohide * 1000));
  // } else {
	  // if(hideclose)
			// dialogclose.style.visibility = "hidden";
	  // else
		   // dialogclose.style.visibility = "visible";
  // }

function showDialog(title,message,type,autohide,hideclose) {
	showDialog(title,message,type,autohide,hideclose, null, null)
}
  
function showDialog(title,message,type,autohide,hideclose, dialogheight, dialogwidth) {
  if(!type) {
    type = 'error';
  }
  var dialog;
  var dialogheader;
  var dialogclose;
  var dialogtitle;
  var dialogcontent;
  var dialogmask;
  var w=window;
  var d=document;
  if(!d.getElementById('dialog')) {
    dialog = d.createElement('div');
    dialog.id = 'dialog';
    dialogheader = d.createElement('div');
    dialogheader.id = 'dialog-header';
    dialogtitle = d.createElement('div');
    dialogtitle.id = 'dialog-title';
    dialogclose = d.createElement('div');
    dialogclose.id = 'dialog-close';
    dialogcontent = d.createElement('div');
    dialogcontent.id = 'dialog-content';
    dialogmask = d.createElement('div');
    dialogmask.id = 'dialog-mask';
    d.body.appendChild(dialogmask);
    d.body.appendChild(dialog);
    dialog.appendChild(dialogheader);
    dialogheader.appendChild(dialogtitle);
    dialogheader.appendChild(dialogclose);
    dialog.appendChild(dialogcontent);
    dialogclose.setAttribute('onclick','hideDialog()');
    dialogclose.onclick = hideDialog;
  } else {
    dialog = d.getElementById('dialog');
    dialogheader = d.getElementById('dialog-header');
    dialogtitle = d.getElementById('dialog-title');
    dialogclose = d.getElementById('dialog-close');
    dialogcontent = d.getElementById('dialog-content');
    dialogmask = d.getElementById('dialog-mask');
    dialogmask.style.visibility = "visible";
    dialog.style.visibility = "visible";
  }
  dialog.style.opacity = .00;
  dialog.style.filter = 'alpha(opacity=0)';
  dialog.alpha = 0;
  var width = pageWidth();
  var height = pageHeight();
  var left = leftPosition();
  var top = topPosition();

  if (dialogwidth == null) {
    dialogwidth = dialog.offsetWidth;
  }
  if (dialogheight == null) {
    dialogheight = dialog.offsetHeight;
  }

  
  var topposition = top + (height / 3) - (dialogheight / 2);
  var leftposition = left + (width / 2) - (dialogwidth / 2);
  dialog.style.top = topposition + "px";
  dialog.style.left = leftposition + "px";
  
  dialog.style.height = dialogheight;
  dialog.style.width = dialogwidth;
  
  dialogheader.className = type + "header";

  dialogheader.style.width = dialogwidth-20;

  dialogtitle.innerHTML = title;
  dialogcontent.className = type;
  dialogcontent.innerHTML = message;

  dialogcontent.style.height = dialogheight-40;
  dialogcontent.style.width = dialogwidth-20;
  dialogcontent.style.overflow = "auto";

  var content = document.getElementById(WRAPPER);
  dialogmask.style.height = content.offsetHeight + 'px';
  dialog.timer = setInterval("fadeDialog(1)", TIMER);
  if(autohide) {
    dialogclose.style.visibility = "hidden";
    window.setTimeout("hideDialog()", (autohide * 1000));
  } else {
	  if(hideclose)
			dialogclose.style.visibility = "hidden";
	  else
		   dialogclose.style.visibility = "visible";
  }
}