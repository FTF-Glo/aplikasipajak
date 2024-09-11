function numbersonly(myfield, e, dec)
{
	var key;
	var keychar;
	if (window.event)
	   key = window.event.keyCode;
	else if (e)
	   key = e.which;
	else
	   return true;
	keychar = String.fromCharCode(key);
	
	// control keys
	if ((key==null) || (key==0) || (key==8) || 
		(key==9) || (key==13) || (key==27) )
	   return true;
	
	// numbers
	else if ((("0123456789").indexOf(keychar) > -1))
	   return true;
	
	// decimal point jump
	else if (dec && (keychar == "."))
	   {
	   myfield.form.elements[dec].focus();
	   return false;
	   }
	else
	   return false;
}

function formatNumber(n,decpoint,gpoint,decdigit){
	var num=''+n;
	var retnum='';
	var pos=num.indexOf('.');
	var intFrac='';
	var decFrac='';
	//alert(n);
	if(pos>-1){
		intFrac=num.substr(0,pos);
		decFrac=num.substr(pos+1,decdigit);
	}
	else{
		intFrac=num;
		decFrac='00';
	}
	if (gpoint==' ')
	{
		retnum=intFrac+(decdigit > 0 ? decpoint : '')+(decdigit > 0 ? decFrac : '');
	}
	else{
		i=intFrac.length-3;
		j=3;
		num='';
		while(i>-3){
			if(i<0){
				j=3+i;
				i=0;
			}
			num=gpoint+intFrac.substr(i,j)+num;
			i-=3;

		}
		num=num.substr(1,num.length-1);
		retnum=num+(decdigit > 0 ? decpoint : '')+(decdigit > 0 ? decFrac : '');
	}
	return retnum;
} // end of formatNumber

function validtopup(topup,sisa,mini,maxi){
	bOK=false;
	sisaakhir=parseInt(topup)+parseInt(sisa);
	if(parseInt(topup)==0){
		alert("Jumlah Top Up tidak boleh 0")
	}else if(sisaakhir<parseInt(mini)){
		alert("Sisa Quota akhir dibawah Quota Minimum");
	}else if(sisaakhir>parseInt(maxi)){
		alert("Sisa Quota akhir diatas Quota Maksimum");
	}else{
			if(confirm("Yakin akan melakukan top-up quota sebesar " + formatNumber(topup,',','.',0)+" ?")){
				bOK=true;
			}			
	}
	return bOK;
}

function validquotasisa(sisa,mini,maxi){
	bOK=false;
	sisaakhir=parseInt(sisa);
	 if(sisaakhir<parseInt(mini)){
		alert("Sisa Quota akhir dibawah Quota Minimum");
	}else if(sisaakhir>parseInt(maxi)){
		alert("Sisa Quota akhir diatas Quota Maksimum");
	}else{
			if(confirm("Yakin akan melakukan perubahan sisa quota menjadi sebesar " + formatNumber(sisa,',','.',0)+" ?")){
				bOK=true;
			}			
	}
	return bOK;
}

function validquotabatas(sisa,mini,maxi){
	bOK=false;
	sisaakhir=parseInt(sisa);
	 if(sisaakhir<parseInt(mini)){
		alert("Sisa Quota akhir dibawah Quota Minimum");
	}else if(sisaakhir>parseInt(maxi)){
		alert("Sisa Quota akhir diatas Quota Maksimum");
	}else{
			if(confirm("Yakin akan melakukan perubahan Batas quota menjadi sebesar " + formatNumber(mini,',','.',0)+ " s/d " +  formatNumber(maxi,',','.',0) +" ?")){
				bOK=true;
			}			
	}
	return bOK;
}

function validtopdown(topdown,sisa,mini,maxi){
	bOK=false;
	sisaakhir=parseInt(sisa)-parseInt(topdown);
	if(parseInt(topdown)==0){
		alert("Jumlah Top Up tidak boleh 0")
	}else if(sisaakhir<parseInt(mini)){
		alert("Sisa Quota akhir dibawah Quota Minimum");
	}else if(sisaakhir>parseInt(maxi)){
		alert("Sisa Quota akhir diatas Quota Maksimum");
	}else{
			if(confirm("Yakin akan melakukan top-down quota sebesar " + formatNumber(topdown,',','.',0)+" ?")){
				bOK=true;
			}			
	}
	return bOK;
}

function ExportExcel(a,mitra,pan,start,end){
		if(pan!=""){
			url="http://"+window.location.host+window.location.pathname.replace("main.php","")+"function/mitra/quota-history-download.php?a="+a+'&m='+mitra+'&p='+pan+'&s='+start+'&e='+end+"&t="+(new Date).getTime();
			//alert(url);
			document.getElementById("download-file").src=url;
		}
}