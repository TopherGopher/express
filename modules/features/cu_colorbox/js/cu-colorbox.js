(function($){
  $(document).ready(function(){
    $("a.colorbox").each(function(){
      var imgAlt = $("img", this).attr("alt");
      $(this).attr("title", imgAlt);
    });
  });
})( jQuery );
