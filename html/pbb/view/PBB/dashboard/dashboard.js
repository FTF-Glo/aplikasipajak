// JavaScript Document
function Fkec(){
	document.getElementById("Pkec").style.display = "block";
	document.getElementById("Pkel").style.display = "none";
	document.getElementById("Pall").style.display = "none";
	uncheck('Ckec[]'); uncheck('Ckel[]');
	uncheck('Ckec_all'); uncheck('Ckel_all'); uncheck('Call');
}
function Fkel(){
	document.getElementById("Pkec").style.display = "none";
	document.getElementById("Pkel").style.display = "block";
	document.getElementById("Pall").style.display = "none";
	uncheck('Ckec[]'); uncheck('Ckel[]');
	uncheck('Ckec_all'); uncheck('Ckel_all'); uncheck('Call');
}
function Fsemua(){
	document.getElementById("Pkec").style.display = "none";
	document.getElementById("Pkel").style.display = "none";
	document.getElementById("Pall").style.display = "block";
	uncheck('Ckec[]'); uncheck('Ckel[]');
	uncheck('Ckec_all'); uncheck('Ckel_all'); checked('Call');
}


function Ftahun(){
	document.getElementById("tgl_1").disabled = true; //DPC_calendar2
}
function Frentang(){
	document.getElementById("tgl_1").disabled = false;
}

function trim(str){
    return str.replace(/^\s+|\s+$/g,'');
}

function validate(){
	tt0 = document.getElementById("tgl_0");
	tt1 = document.getElementById("tgl_1");
	if(tt1.disabled==false && trim(tt1.value)==""){
		tt1.disabled=true;
	}else if(tt1.disabled==false && trim(tt1.value)!=""){
		if(parseInt(tt0.value) > parseInt(tt1.value)){
			alert("penulisan rentang tahun tidak benar");
			return false;
		}
	}
}

function iniAngka(evt){
         var charCode = (evt.which) ? evt.which : event.keyCode
         if ( charCode >= 48 && charCode <= 57 )
            return true;

         return false;
}
	  
function check(field,name){
	if(field.checked == true){
		arr = document.getElementsByName(name);
		for (i = 0; i < arr.length; i++)
		arr[i].checked = true;
	}else{
		arr = document.getElementsByName(name);
		for (i = 0; i < arr.length; i++)
		arr[i].checked = false;
	}
}
function uncheck(name){
		arr = document.getElementsByName(name);
		for (i = 0; i < arr.length; i++)
		arr[i].checked = false;
}
function checked(name){
		arr = document.getElementsByName(name);
		for (i = 0; i < arr.length; i++)
		arr[i].checked = true;
}
function disFlag(elm){
		val = elm.value;
		if(val==2){
		  document.getElementById("flag_100").disabled = true;
		  document.getElementById("flag_0").disabled = true;
		  document.getElementById("flag_1").disabled = true;
		}else{
		  document.getElementById("flag_100").disabled = false;
		  document.getElementById("flag_0").disabled = false;
		  document.getElementById("flag_1").disabled = false;
		}
}

var xmlhttp;
function loadXMLDoc(url,cfunc){
	if (window.XMLHttpRequest){ 
	  	xmlhttp=new XMLHttpRequest(); // code for IE7+, Firefox, Chrome, Opera, Safari
	}else{
	  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP"); // code for IE6, IE5
	}
		xmlhttp.onreadystatechange=cfunc;
		xmlhttp.open("GET",url,true);
		xmlhttp.send();
}
function getDataDayMonth(kota){ 
	loadXMLDoc("./view/PBB/dashboard/svc-get-data.php?nmKota="+kota,function(){
	  if (xmlhttp.readyState==4 && xmlhttp.status==200){ 
	  		txt = xmlhttp.responseText;
			data = JSON.parse(txt);
			//alert(data[1]['title']);
			
			title = data[1]['title'];
			subTitle = data[1]['subTitle'];
			series = data[1]['series'];
			xAxisData =data[1]['xAxisData'];
			yAxisTitle = data[1]['yAxisTitle']; 
			getChart("containerChart",title,subTitle,series,xAxisData,yAxisTitle);
			
			title = data[0]['title'];
			subTitle = data[0]['subTitle'];
			series = data[0]['series'];
			xAxisData =data[0]['xAxisData'];
			yAxisTitle = data[0]['yAxisTitle']; 
			getChart("containerChart2",title,subTitle,series,xAxisData,yAxisTitle);
		} //xmlhttp.responseText
	  });
}

function getChart(containerChart,title,subtitle,seriesDat,xAxisData,yAxisTitle){
	seriesDat = eval(seriesDat) ;
	xAxisData = eval(xAxisData) ; 
	chart = new Highcharts.Chart({
            chart: {
                renderTo: containerChart,
                type: 'column'
				//events: { load: function() { setInterval(function() { getDataDayMonth(''); }, 1000*10); } }
            },
            title: {
                text: title
            },
            subtitle: {
                text: subtitle
            },
            xAxis: {
                categories: xAxisData 
            },
            yAxis: {
                min: 0,
                title: {
                    text: yAxisTitle
                }
            },
            legend: {
                layout: 'vertical',
                backgroundColor: '#FFFFFF',
                align: 'left',
                verticalAlign: 'top',
                x: 390,
                y: 10,
                floating: true,
                shadow: true
            },
            tooltip: {
                formatter: function() {
                    return ''+
                        this.x +': '+ this.y +' Rupiah';
                }
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: seriesDat,
			credits: {
			enabled: false
		}
        });
}
function getChartBar(containerChart,title,subtitle,seriesDat,xAxisData,yAxisTitle,satuan){
	seriesDat = eval(seriesDat) ;
	xAxisData = eval(xAxisData) ; 
	chart = new Highcharts.Chart({
            chart: {
                renderTo: containerChart,
                type: 'bar'
            },
            title: {
                text: title
            },
            subtitle: {
                text: subtitle
            },
            xAxis: {
                categories: xAxisData, // xAxisData ['Africa', 'America', 'Asia', 'Europe', 'Oceania']
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: yAxisTitle, // 'Population (millions)',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                formatter: function() {
                    return ''+
                        this.series.name +': '+ this.y +' '+satuan;
                }
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'top',
                x: 0,
                y: 0,
                floating: true,
                borderWidth: 1,
                backgroundColor: '#FFFFFF',
                shadow: true
            },
            credits: {
                enabled: false
            },
            series: seriesDat // seriesDat		[{ name: '2001', data: [10, 31, 63, 20, 20] },{ name: '2003', data: [97, 91, 40, 73, 34] }]
        });
}
function getChartPie(containerChart,title,subtitle,seriesDat){
	seriesDat = eval(seriesDat) ;

	chart = new Highcharts.Chart({
            chart: {
                renderTo: containerChart,
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: true
            },
            title: {
                text: title,
				style: {
                fontSize: '12px'
            	}
            },
			subtitle: {
                text: subtitle
            },
            tooltip: {
                formatter: function() {
                    return '<b>'+ this.point.name +'</b>: '+ this.percentage +' %';
                }
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#000000',
                        formatter:false
                    }
                }
            },
            series: [{
				type: 'pie', 
				name: 'Browser share', 
				data: seriesDat //[ ['Data1',   60], ['Data2', 20] ] 
				}]
        });
}
//setInterval(function() {	getDataDayMonth('Bandung');	  }, 1000*10);