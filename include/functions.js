(function($){
  OndemandMp4Loading = function() {
    var root = $("body").find('h2.trigger');
    //alert("Ciao pippo");
    root.each(function( index )
    {
	//alert( index + " GroupId: " + $(this).next('.toggle_container').attr('id'));
	if ( $(this).hasClass("active") )
	{
	    divleft = $(this).next('div.toggle_container').find('div.left');
	    var divleft_id = divleft.attr('id');
	    //alert("Oggetto: " + divleft_id);
	    	    
	    divleft.children().each(function( index )
	    {
		var iphoneobj = $(this).find(".player_iphone:first");
		var videoloadobj = $(this).find(".video_loading:first");
		var iphone_href = iphoneobj.children().first('a').attr('href');
		var iphone_id = iphoneobj.attr('id');
	
		if (iphoneobj.hasClass("active"))
		{
		    //alert(index + " già controllato e attivo: " + iphone_id);
		    return;
		}
		
		/************************************************************************
		 *** Controllo se esiste il file .mp4 nella cartella della congregazione
		 ***********************************************************************/
		var uri =  iphone_href.substr(4);
		var fullurl = 'http://' + document.location.hostname + '/mp4/' + divleft_id + uri;
		
		$.ajax({
		    url: fullurl,
		    type:'HEAD',
		    error:
			function(){
			    iphoneobj.removeClass("active");
			    iphoneobj.hide();
			    videoloadobj.hide();
			    return;
			    //alert("Url [ " + fullurl + " ] FAILED.");
			},
		    success:
			function(){
			    //alert("Url [ " + fullurl + " ] SUCCESS.");
			}
		});
		
		/**************************************************************************
		 *** Controllo se la richiesta http al link .mp4 risponde correttamente ***
		 *************************************************************************/
		link_url = "http://" + document.location.hostname + iphone_href;
		$.ajax({
		    url: link_url,
		    type:'HEAD',
		    error:
			function(){
			    iphoneobj.removeClass("active");
			    iphoneobj.hide();
			    videoloadobj.show();
			    //alert("Url [ " + link_url + " ] FAILED.");
			},
		    success:
			function(){
			    videoloadobj.hide();
			    iphoneobj.show();
			    iphoneobj.addClass("active");
			    //alert("Url [ " + link_url + " ] SUCCESS.");
			}
		});
		
		});
	}
    });
    
    //$('.player_iphone').load('ondemand_apple_div_load.php?url="' + $(this).attr('id') + '"').fadeIn("slow");    
  };
})(jQuery);