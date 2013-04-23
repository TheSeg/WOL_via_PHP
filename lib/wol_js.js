/*

*/
// This AJAX-function retrieves the value of the "Manufacturer of NIC" field.
function showValue(str) {
    //console.log("showVal2("+str+")");
    jQuery.get(
        "NIC_manufacturer.php",
        'ajaxVariable='+str+"",
        function(data) {
            $('#ajaxDIV').html(data);
            //console.log(data);
        }
    );
}


// Document Ready Handler
$(document).ready(function() {
    // Fill out default values, if exists.
    if ( $('#ajaxDIV').is('*') > 0 ) {
        showValue( $('#WOL_mac_address').val() );
    }
    if ( $('#WOL_mac_address').is('*') > 0 ) {
        $('#WOL_mac_address').change(
            function() {
                showValue($('#WOL_mac_address').val());
            }
        );
    }
});