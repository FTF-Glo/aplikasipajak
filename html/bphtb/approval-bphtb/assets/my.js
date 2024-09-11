function postAjax(url, data, success) {
    var params = typeof data == 'string' ? data : Object.keys(data).map(
        function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
    ).join('&');

    var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
    xhr.open('POST', url);
    xhr.onreadystatechange = function () {
        if (xhr.readyState > 3 && xhr.status == 200) { success(xhr.responseText); }
    };
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send(params);
    return xhr;
}

/** datatables */
// var pelaporanTable = new DataTable("#pelaporanTable");

function changeStatus(id_ssb) {
    if (confirm('Tindakan anda akan mengembalikan status persetujuan. Apakah anda yakin ?')) {
        postAjax(BASEURL + '?action=approval', {
            type: 3,
            id_ssb: id_ssb
        }, function (data) {
            location.reload();
        })
    }
}
function reject(id_ssb) {
    if (confirm('Tindakan anda tidak dapat dikembalikan. Apakah anda yakin ?')) {
        let msg = prompt('Berikan alasan lalu tekan OK untuk lanjut dan Cancel untuk batal');
        if (msg == null) {
            return;
        }

        postAjax(BASEURL + '?action=approval', {
            type: 2,
            id_ssb: id_ssb,
            msg: msg
        }, function (data) {
            location.reload();
        })
    }
}
function approve(id_ssb) {
    if (confirm('Tindakan anda tidak dapat dikembalikan. Apakah anda yakin ?')) {
        postAjax(BASEURL + '?action=approval', {
            type: 1,
            id_ssb: id_ssb
        }, function (data) {
            location.reload();
        })
    }
}
