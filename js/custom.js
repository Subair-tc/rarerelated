jQuery( document ).ready(function() {
    jQuery('.post_types_related').on('change',function(){ 
        jQuery(this).parent('.form-group').next('.post_type_details').toggleClass('active');
    });


    function myFunction() {
    var input, filter, ul, li, a, i;
    input = document.getElementById("myInput");
    filter = input.value.toUpperCase();
    ul = document.getElementById("myUL");
    li = ul.getElementsByTagName("li");
    for (i = 0; i < li.length; i++) {
        a = li[i].getElementsByTagName("a")[0];
        if (a.innerHTML.toUpperCase().indexOf(filter) > -1) {
            li[i].style.display = "";
        } else {
            li[i].style.display = "none";

        }
    }
}



});


 function getRareRelated( id, item_list_id ) {

    var val = jQuery( id ).val().toUpperCase();
    var items = jQuery(item_list_id + " .form-group");
    //console.log(items);

    jQuery.each( items,function( index, value ) {

       // console.log(value);
        //jQuery(value).html()

        if (jQuery(value).html().toUpperCase().indexOf(val) > -1) {
            jQuery(value).css('display','block');
        } else {
             jQuery(value).css('display','none');
        }
    });
}