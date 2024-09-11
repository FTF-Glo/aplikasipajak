<script type='text/javascript' src='ext-core.js'></script>

ISOClient = Ext.extend(Object, {
    constructor: function(first, last){
        this.firstName = first;
        this.lastName = last;
    },

    getName: function(){
        return this.firstName + ' ' + this.lastName;
    },
	
	sendISORequest: function(htmlElement, sel){
		//new request
		Ext.Ajax.request({
			url: 'ajax_demo/sample.json',
			success: function(response, opts) {
				var obj = Ext.decode(response.responseText);
				console.dir(obj);
				// alert('berhasil ' + sel);
				htmlElement.insertHtml('afterBegin', '<p>Berhasil</p>');
			},
			failure: function(response, opts) {
				console.log('server-side failure with status code ' + response.status);
				// alert('gagal ' + sel);
				htmlElement.insertHtml('afterBegin', '<p>Gagal</p>');
			}
		});
	}
});
