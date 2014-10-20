(function($){
  
  OndemandMp4Loading = function() {
    var root = $("div.panel-group").find('div.panel-collapse');
    //alert("Ciao pippo");
    root.each(function( index )
    {
	//alert( index + " GroupId: " + $(this).attr('class'));
	
	if ( $(this).hasClass("collapse in") )
	{
	    divleft = $(this).find('.panel-body').find('div.left');
	    var divleft_id = divleft.attr('id');
	    //alert("Oggetto: " + divleft_id);
	    
	    divleft.children().each(function( index )
	    {
		var ulobj = $(this).next('ul');
		ulobj.each(function( subindex )
		{
		    var iphoneobj = ulobj.find(".player_iphone:first");
		    var videoloadobj = ulobj.find(".video_loading:first");
		    
		    //alert(index + " - " + subindex + " Oggetto: " + iphoneobj.attr('id') + " " + videoloadobj.attr('id'));
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
				//alert("CONTROLLO CARTELLA:\nUrl [ " + fullurl + " ] FAILED.");
				return;
			    },
			success:
			    function(){
				//alert("CONTROLLO CARTELLA:\nUrl [ " + fullurl + " ] SUCCESS.");
			    }
		    });
		    
		    /**************************************************************************
		     *** Controllo se la richiesta http al link .mp4 risponde correttamente ***
		     *************************************************************************/
		    
		    link_url = "http://" + document.location.hostname + iphone_href;
		    //alert("CONTROLLO WEB URL:\nUrl [ " + link_url + " ]");
		    /*
		    $.ajax({
			url: link_url,
			type:'HEAD',
			error:
			    function(){
				iphoneobj.removeClass("active");
				iphoneobj.hide();
				videoloadobj.show();
				alert("CONTROLLO WEB:\nUrl [ " + link_url + " ] FAILED.");
			    },
			success:
			    function(){
				videoloadobj.hide();
				iphoneobj.show();
				iphoneobj.addClass("active");
				//alert("Url [ " + link_url + " ] SUCCESS.");
			    }
		    });
		    */
		  });
		
	    });
	}
    });
    
  };
})(jQuery);