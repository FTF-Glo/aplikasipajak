(function(){
    ket_pelapora_ke();
    ket_noppelapora_ke();
})()


function ket_pelapora_ke() {
    var noktp = document.getElementById("noktp").value;
    var nop = document.getElementById("name2").value;
    var trsid = document.getElementById("trsid").value;
    Ext.Ajax.request({
        url : './function/BPHTB/notaris/svc-cek-pelaporan.php',
        method: 'POST',
        params: {noktp: noktp,nop:nop,trsid:trsid},
        success: function(result, request) {
            var jsonData = JSON.parse(result.responseText);
            if (jsonData.success) {
                console.log(jsonData.feedback)
                $("#ketlaporan").html(jsonData.feedback)
            }
        },
        failure: function(result, request) {

        }
    });
}
function ket_noppelapora_ke() {
    var noktp = document.getElementById("noktp").value;
    var nop = document.getElementById("name2").value;
    var trsid = document.getElementById("trsid").value;
    Ext.Ajax.request({
        url : './function/BPHTB/notaris/svc-cek-noppelaporan.php',
        method: 'POST',
        params: {noktp: noktp,nop:nop,trsid:trsid},
        success: function(result, request) {
            var jsonData = JSON.parse(result.responseText);
            if (jsonData.success) {
                console.log(jsonData.feedback)
                $("#ketlaporan2").html(jsonData.feedback)
            }
        },
        failure: function(result, request) {

        }
    });
}

