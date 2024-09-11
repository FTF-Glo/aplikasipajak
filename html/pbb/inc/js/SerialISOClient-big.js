SerialISOClient = Ext.extend(Object, {
 	//konstruktor
	constructor: function(url){
		this.serverURL = url;
        this.requestQueue = new Array();
		this.timeout = 20000;
    },

	//getter/setter
    getURL: function(){
        return this.serverURL;
    },

    setURL: function(newUrl){
        this.serverURL = newUrl;
    },

    getTimeout: function(){
        return this.timeout;
    },

    setTimeout: function(newTimeout){
        this.timeout = newTimeout;
    },
	
	getRequestCount: function() {
		return this.requestQueue.length;
	},
	
	//methods
	//menambat request dari depan antrian
	//tiap request dimasukkan ke dalam array dengan 
	//[0] = parameter request url ajax
	//[1] = parameter fungsi berhasil / gagal
	//[2] = fungsi berhasil
	//[3] = fungsi gagal
	unshiftISORequest: function(requestParams, functionParams, successFunction, failureFunction) {
		this.requestQueue.unshift(new Array(requestParams, functionParams, successFunction, failureFunction));
		if (!this.isRequesting) {
			this.doISORequest();
		}
	},

	//menambat request dari belakang antrian
	pushISORequest: function(requestParams, functionParams, successFunction, failureFunction) {
			// alert('aneh...');
		this.requestQueue.push(new Array(requestParams, functionParams, successFunction, failureFunction));
		if (!this.isRequesting) {
			this.doISORequest();
		}
	},

	//fungsi rekursif menjalankan antrian ajax request, berhenti ketika array request kosong
	doISORequest: function() {
		// alert('command ke-'+this.requestQueue.length);
		this.currentRequest = this.requestQueue.shift();
		if (this.currentRequest != null) {
			// membentuk url request ajax
			// var tempUrl = this.serverURL;
			// if (this.currentRequest[0].length != 0) {
				// tempUrl += '?' + this.currentRequest[0][0][0] + '=' + this.currentRequest[0][0][1];
				// for (i = 1; i < this.currentRequest[0].length ; i++) {
					// tempUrl += '&' + this.currentRequest[0][i][0] + '=' + this.currentRequest[0][i][1];
				// }			
			// }
			// var tempTimeout = this.timeout;

			// alert('url jadian: '+tempUrl);

			var tempThis = this;
			this.isRequesting = true;
			Ext.Ajax.request({
				url: tempThis.serverURL,
				method :'POST',
				success: function(response, opts) {
					// alert('berhasil ' + tempThis.serverURL);
					tempThis.currentRequest[2](response, tempThis.currentRequest[1]);
					tempThis.isRequesting = false;
					tempThis.doISORequest();
				},
				failure: function(response, opts) {
					// alert('gagal ' + tempThis.serverURL);
					tempThis.currentRequest[3](response, tempThis.currentRequest[1]);
					tempThis.isRequesting = false;
					tempThis.doISORequest();
				},
				params: tempThis.currentRequest[0],
				timeout: tempThis.timeout
			});
		}
	}

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
});
