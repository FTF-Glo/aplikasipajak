SerialAJAXClient = Ext.extend(Object, {
 	//konstruktor
	constructor: function(){
        this.requestQueue = new Array();
    },

	//getter/setter
    // getURL: function(){
        // return this.serverURL;
    // },

    // setURL: function(newUrl){
        // this.serverURL = newUrl;
    // },
	
	//methods
	//menambat request dari depan antrian
	//tiap request dimasukkan ke dalam array dengan 
	//[0] = request url ajax
	//[1] = parameter request url ajax
	//[2] = parameter fungsi berhasil / gagal
	//[3] = fungsi berhasil
	//[4] = fungsi gagal
	unshiftISORequest: function(requestUrl, requestParams, functionParams, successFunction, failureFunction) {
		this.requestQueue.unshift(new Array(requestUrl, requestParams, functionParams, successFunction, failureFunction));
		if (!this.isRequesting) {
			this.doISORequest();
		}
	},

	//menambat request dari belakang antrian
	pushISORequest: function(requestUrl, requestParams, functionParams, successFunction, failureFunction) {
			// alert('aneh...');
		this.requestQueue.push(new Array(requestUrl, requestParams, functionParams, successFunction, failureFunction));
		if (!this.isRequesting) {
			this.doISORequest();
		}
	},

	//fungsi rekursif menjalankan antrian ajax request, berhenti ketika array request kosong
	doISORequest: function() {
		alert('command ke-'+this.requestQueue.length);
		this.currentRequest = this.requestQueue.shift();
		if (this.currentRequest != null) {
			// membentuk url request ajax
			var tempUrl = this.currentRequest[0];
			if (this.currentRequest[1].length != 0) {
				tempUrl += '?' + this.currentRequest[1][0][0] + '=' + this.currentRequest[1][0][1];
				for (i = 1; i < this.currentRequest[1].length ; i++) {
					tempUrl += '&' + this.currentRequest[1][i][0] + '=' + this.currentRequest[1][i][1];
				}			
			}

			alert('url jadian: '+tempUrl);

			var tempThis = this;
			this.isRequesting = true;
			Ext.Ajax.request({
				url: tempUrl,
				success: function(response, opts) {
					// alert('berhasil ' + tempThis.serverURL);
					tempThis.currentRequest[3](response, tempThis.currentRequest[2]);
					tempThis.isRequesting = false;
					tempThis.doISORequest();
				},
				failure: function(response, opts) {
					// alert('gagal ' + tempThis.serverURL);
					tempThis.currentRequest[4](response, tempThis.currentRequest[2]);
					tempThis.isRequesting = false;
					tempThis.doISORequest();
				}
			});
		}
	},
	
	// doISORequestProxy: function() {
		// this.doISORequest();
	// },

	// sendISORequest: function(htmlElement, sel){
		// //new request
		// Ext.Ajax.request({
			// url: 'ajax_demo/sample.json',
			// success: function(response, opts) {
				// var obj = Ext.decode(response.responseText);
				// console.dir(obj);
				// console.dir(opts);
				// // alert('berhasil ' + sel);
				// htmlElement.insertHtml('afterBegin', '<p>Berhasil</p>');
			// },
			// failure: function(response, opts) {
				// console.log('server-side failure with status code ' + response.status);
				// console.dir(response);
				// console.dir(opts);
				// // alert('gagal ' + sel);
				// htmlElement.insertHtml('afterBegin', '<p>Gagal</p>');
			// }
		// });
	// }
})