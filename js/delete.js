$(document).ready(function(){
    
});
function delete_confirm(id){
    $('#delete_confirm_'+id).dialog({
      resizable: false,
      height: "auto",
      width: 400,
      modal: true,
      buttons: {
        "Delete": function() {
          $( this ).dialog( "close" );
          $(window).load(M.cfg.wwwroot+'/local/learningplan/index.php?id='+id+'&delete=1&sesskey='+M.cfg.sesskey);
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
    });
}