function scrollmarquee()  {
				if (parseInt(CROSS_MARQUEE.style.left)>(ACTUALWIDTH*(-1)-100))
						  CROSS_MARQUEE.style.left=(parseInt(CROSS_MARQUEE.style.left)-COPYSPEED)+"px";
				else{
						  CROSS_MARQUEE.style.left=(parseInt(document.body.offsetWidth)+8)+"px";
						  if(CURRENTMSGIDX<(BROADCASTMSGS.length-1)){
							CURRENTMSGIDX++;
						  }
						  else
							CURRENTMSGIDX=0;
						  CROSS_MARQUEE.innerHTML=BROADCASTMSGS[CURRENTMSGIDX];
						  document.getElementById("temp").innerHTML=BROADCASTMSGS[CURRENTMSGIDX];
						  ACTUALWIDTH=document.getElementById("temp").offsetWidth;
						  ACTUALWIDTH+=400;
						  CROSS_MARQUEE.style.width=ACTUALWIDTH+"px";
			}
		}

		function getBROADCASTMSGS(){
						SCANPayment.Payment.dsMsg.load({params:{svrdt:SCANPaymentPPPreferences.dt.format('Y-m-d H:i:s')}});
		}

		function makeRepeatedString(str,n)
		{
						  s='';
						  for(i=0;i<n;i++)
							s+=str;
						  return s;
		}
			
		var MSGtID;
		var MARQUEE_SPEED=1;
		var MARQUEE_PAUSEIT=1;
		var COPYSPEED=MARQUEE_SPEED;
		var PAUSESPEED=(MARQUEE_PAUSEIT==0)?COPYSPEED: 0;		
		var ACTUALWIDTH='';
		var CURRENTMSGIDX=-1;
		var CROSS_MARQUEE=document.getElementById("iemarquee");
		var BROADCASTMSGS=Array();

		//GetBroadcastMessage();
		function init(){
			CROSS_MARQUEE=document.getElementById("iemarquee");
			window.clearInterval(MSGtID);
			if(BROADCASTMSGS.length>0){
				MSGtID=window.setInterval(scrollmarquee,20);
			}
		}

		function displayMessages(){
			if(BROADCASTMSGS.length>0){
				strMsg="";
				for(var i=0;i<BROADCASTMSGS.length;i++){
					strMsg+=BROADCASTMSGS[i]+"<br/><br/>"
				}
				showDialog("Daftar Pesan",strMsg,"prompt")
			}
		}