function Ajax() {
	var ajax = this;
    this.xml = null;
    this.async = true;
    this.callBack = null;    
    this.GetXmlHttp = function() {
		try {
			this.xml = new ActiveXObject('Msxml2.XMLHTTP');
		} catch (e) {
			try {
				this.xml = new ActiveXObject('Microsoft.XMLHTTP');
			} catch (e2) { this.xml = false; }
		}
		if (!this.xml && typeof XMLHttpRequest != 'undefined') { this.xml = new XMLHttpRequest(); }
	}
    this.GetXmlHttp();
    this.updatePage = function () {
        if (this.readyState == 4&&this.status == 200) {
            if (ajax.callBack != null && typeof ajax.callBack == 'function') { ajax.callBack(this.responseText); }
        }
    }
    this.toQueryString = function (json) {
        var query = '';
        if (json != null) {
            for (var param in json) { query += param + '=' + encodeURIComponent(json[param]) + '&' }
        }
        return query;
    }
    this.invoke = function (opName, params, callback) {
        if (!this.xml) return;
        var query = '';
        query += this.toQueryString(params);
        query = query.substring(0, query.length - 1);
        this.callBack = callback;
        this.xml.onreadystatechange = this.updatePage;
        this.xml.open('POST', location.pathname +'?op='+opName, this.async);
        this.xml.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        this.xml.send(query);
    }
}