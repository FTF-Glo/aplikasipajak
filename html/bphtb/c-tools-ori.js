function GetZeroPrefixNumber(iNumber, nDigit)
{
  var sZeroPrefix = '';
  var sNumber = iNumber + '';
  n = sNumber.length;
  if (nDigit > n){
    for (i=0; i<(nDigit-n); i++) {
		sZeroPrefix += '0';
	}
  }
  sNumber = sZeroPrefix + sNumber;
  return sNumber;
} // end of GetZeroPrefixNumber

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

function MySortNumber(a, b)
{
  return a[1] - b[1];
} // end of MySortNumber

function InsertArrayToSortedArray(aSort, aElmt, nMax)
{
  n = aSort.push(aElmt);
  aSort.sort(MySortNumber);
  if (n > nMax)
  {
    aSort.shift();
  }
} // end of InsertIntegerToSortedArray

function PrintArray(aSort)
{
  // print
  var s = '';
  for (i=0; i<aSort.length; i++)
  {
    s += '[' + aSort[i][0] + ']-[' + aSort[i][1] + ']';
  }
  alert(s);
}

// aSort is already sorted ascending
function ConcatArrayElements(aSort, iIdx, sDelim, bAsc)
{
  var s = '';
  var aSortNew = new Array();
  for (var i=0; i<aSort.length; i++)
  {
    aSortNew[i] = new Array();
    for (var j=0; j<aSort[i].length; j++)
      aSortNew[i][j] = aSort[i][j];
  }
  if (!bAsc) aSortNew = aSortNew.reverse();
  for (i=0; i<aSortNew.length; i++)
    s += aSortNew[i][iIdx] + sDelim;

  return s;
} // end of ConcatArrayElements