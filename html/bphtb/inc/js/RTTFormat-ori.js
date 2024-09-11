function clearFormat (input, milSep){
	var strCheck = '0123456789';
	var clstr = "";

	len = input.value.length;
	for(i=0; i < len; i++)
		if (strCheck.indexOf(input.value.charAt(i))!=-1) clstr += input.value.charAt(i);	

	return clstr;
}

function filterInput(input, e){
	if(e != undefined){
		var strCheck = '0123456789';
		var whichCode = (window.Event) ? e.which : e.keyCode;
		if (whichCode == 8) return true;  // Delete
		//VALIDATION
		key = String.fromCharCode(whichCode);  // Get key value from key code
		if (strCheck.indexOf(key) == -1) return false;  // Not a valid key	
	}
	if (input.value.charAt(0) == '0') return false;	// block 0 input
}

function applySeparator(str, milSep){
	st = "";
	length = str.length;
	j = 0;
	for (i = length - 1; i >= 0; i--)
	{
		j++;
		if (j > 3)
		{
			st = milSep + st;
			j = 1; 
		}
		
		ch = str.charAt(i);
		st = ch + st;
	}
	return st;
}

// When using this function at onChange event, use filterInput in onKeyPress
// Default using at onBlur
function currencyFormatIC (input, milSep) {
	if (input.value.charAt(0) == '0') return false;	// block 0 input	
	aux = clearFormat(input,milSep);
	input.value = applySeparator(aux, milSep);
}


// OnKeyPress, execute before character code displayed 
function currencyFormatI(input, milSep, e) {
	var sep = 0;
	var key = '';
	var i = j = 0;
	var len = len2 = 0;
	var strCheck = '0123456789';
	var aux = aux2 = '';
	var whichCode = (window.Event) ? e.which : e.keyCode;	

	len = input.value.length;

	
	//VALIDATION
	key = String.fromCharCode(whichCode);	// Get key value from key code
	
	if (whichCode == 8)	{ //Backspace
		if(input.value.length != 0){ // Fix all delimiters place before backspace
			for(i=0; i < len; i++) // Trim character other than number
				if (strCheck.indexOf(input.value.charAt(i))!=-1) aux += input.value.charAt(i);
			len = aux.length;
			aux2 = '';
			j = 0;
			first=true;
			for (i = len; i >= 0; i--) {
				if ((j == 5) && (first==true)) {
					first = false;
					aux2 += milSep;
					j = 0;
				}
				if ((j == 3) && (first==false)) {
					aux2 += milSep;
					j = 0;
				}
				aux2 += aux.charAt(i);
				j++;
			}

			//console.log("aux2:"+ aux2);
		    input.value = '';
		    len2 = aux2.length;
		    for (i = len2; i >= 0; i--)
				input.value += aux2.charAt(i);
			
		}
		return true;
	}
	if (input.value.charAt(0) == '0') return false; // block 0 input
	if (strCheck.indexOf(key) == -1) return false; // not a valid key

	len = input.value.length; // check maxLength
	maxlen = input.maxLength;
	if (maxlen == len) return false;
	if (maxlen % 4 == 0){
		if(len == maxlen-1)	
			return false;
	}
	
	
	aux = '';
	for(i=0; i < len; i++)
		if (strCheck.indexOf(input.value.charAt(i))!=-1) aux += input.value.charAt(i);
	
	len = aux.length;
	aux2 = '';
	
	j = 0; k = 0;
	for (i = len; i >= 0; i--) {
		if (j == 3) {
			aux2 += milSep;
			j = 0;
		}
		aux2 += aux.charAt(i);
		j++;
		k++;
	}
    input.value = '';
    len2 = aux2.length;
    for (i = len2 - 1; i >= 0; i--)
		input.value += aux2.charAt(i);
}

function onSelectClearFormat(input, milSep, e){
	input.value = clearFormat(input, milSep);
	return filterInput(input, e);
}