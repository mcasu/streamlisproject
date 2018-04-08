(function($){
  
OndemandMp4Loading = function() 
{
  //var root = $("div.panel-group").find('div.panel-collapse');
  var root = $("div.panel-group");
  //alert("Ciao pippo");
  root.children().each(function( index )
  {
      var panelobj = $(this).find('div.panel-collapse');
      //alert( index + " Pannello con GroupId: " + panelobj.attr('id'));

      if ( panelobj.hasClass("collapse in") )
      {
          ulleft = panelobj.find('ul.checked-list-box');
          var ulleft_id = ulleft.attr('id');
          alert("Oggetto ulleft: " + ulleft_id);

          panelobj.find('li.video_list_element').each(function( index )
          {
              var titleobj = $(this).find('div.video_element_title');
              //alert("Element ID [ " + titleobj.attr('id') + " ] - Element CLASS [ " + titleobj.attr('class') + " ]");

              var ulobj = $(this).find('ul');
              var iphoneobj = ulobj.find(".player_iphone:first");
              var videoloadobj = ulobj.find(".video_loading:first");
              var videoDownloadObj = ulobj.find(".video_download:first");

              //alert(index + " - Oggetto: " + iphoneobj.attr('id') + " " + videoloadobj.attr('id'));

              var iphone_href = iphoneobj.children().first('a').attr('href');
              //alert(index + " - Iphone link href: " + iphone_href);

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
              var fullurl = 'https://' + document.location.hostname + '/mp4/' + ulleft_id + '/' + iphone_id + ".mp4";
              console.log("Check [" + fullurl + "]");

              $.ajax({
                  url: fullurl,
                  type:'HEAD',
                  cache: false,
                  error:
                      function(){
                          iphoneobj.removeClass("active");
                          iphoneobj.addClass("notavailable");
                          iphoneobj.hide();
                          videoloadobj.hide();
                          videoDownloadObj.hide();
                          //alert("CONTROLLO CARTELLA:\nUrl [ " + fullurl + " ] FAILED.");
                          return;
                      },
                  success:
                      function(){
                          iphoneobj.removeClass("notavailable");
                          //alert("CONTROLLO CARTELLA:\nUrl [ " + fullurl + " ] SUCCESS.");
                      }
              });


              if (iphoneobj.hasClass("notavailable"))
              {
                  return;
              }

              /**************************************************************************
               *** Controllo se la richiesta http al link .mp4 risponde correttamente ***
               *************************************************************************/
              //link_url = "https://" + document.location.hostname + '/mp4/' + ulleft_id + '/' + iphone_id;
              
              //alert("CONTROLLO WEB URL:\nUrl [ " + link_url + " ]");

              $.ajax({
                  url: fullurl,
                  type:'HEAD',
                  cache: false,
                  error:
                      function(){
                          iphoneobj.removeClass("active");
                          iphoneobj.hide();
                          videoDownloadObj.hide();
                          videoloadobj.show();
                          //alert("CONTROLLO WEB:\nUrl [ " + link_url + " ] FAILED.");
                      },
                  success:
                      function(){
                          videoloadobj.hide();
                          iphoneobj.show();
                          videoDownloadObj.show();
                          iphoneobj.addClass("active");
                          //alert("Url [ " + link_url + " ] SUCCESS.");
                      }
              });

          });
      }
  });

};
  
CheckLiveExistsForPublishCode = function(publishCode) 
{
    var params = "fname=check_live_exists_for_publish_code&publishCode=" + publishCode;
    var result;
    jQuery.ajax({
        type: "POST",
        url: "/include/functions.php",
        data: params,
        async: false,
        cache: false,
        success: function(res)
        {
            if(res === "true")
            {
                //alert('La congregazione con code [' + publishCode + '] sta trasmettendo - ' + res);
            }
            else
            {
                //alert('La congregazione con code [' + publishCode + '] NON sta trasmettendo - ' + res);
            }
            
            result = res;
        }
    });
    
    return result;
};

ExecActionsVideoConversion = function(acid) 
{
    var params = 'acid=' + acid;
    var result;
    jQuery.ajax({
        type: "POST",
        url: "/cron/ondemand_convert_video.php",
        data: params,
        async: false,
        cache: false,
        success: function(res)
        {
            result = res;
        }
    });
    
    return result;
};

GetActionsConvertIdByOndemandId = function(ondemandId) 
{
    var params = 'fname=get_actions_convertid_by_ondemandid&ondemandId=' + ondemandId;
    var result;
    jQuery.ajax({
        type: "POST",
        url: "/include/functions.php",
        data: params,
        async: false,
        cache: false,
        success: function(res)
        {
            result = res;
        }
    });
    
    return result;
};

MarkOndemandVideoToConvert = function(ondemandIdList, userId) 
{
    var params = 'fname=mark_ondemand_video_to_convert&ondemandIdList=' + ondemandIdList + '&userId=' + userId;
    var result;
    jQuery.ajax({
        type: "POST",
        url: "/include/functions.php",
        data: params,
        async: false,
        cache: false,
        success: function(res)
        {
            if(res === "true")
            {
                //alert('RISULTATO: ' + res);
            }
            else
            {
                //alert('RISULTATO: ' + res);
            }
            result = res;
        }
    });
    
    return result;
};

MarkOndemandVideoToJoin = function(ondemandIdList, userId) 
{
    var params = 'fname=mark_ondemand_video_to_join&ondemandIdList=' + ondemandIdList + '&userId=' + userId;
    var result;
    jQuery.ajax({
        type: "POST",
        url: "/include/functions.php",
        data: params,
        async: false,
        cache: false,
        success: function(res)
        {
            if(res === "true")
            {
                //alert('RISULTATO: ' + res);
            }
            else
            {
                //alert('RISULTATO: ' + res);
            }
            result = res;
        }
    });
    
    return result;
};
   
StreamVideoSizeUpdate = function() 
{
    var root = $("div.panel-group");
    root.children().each(function( index )
    {
        var panelobj = $(this).find('div.panel-collapse');
        //console.log( index + " Pannello con GroupId: " + panelobj.attr('id'));

        if ( panelobj.hasClass("collapse in") )
        {
            ulVideoElement = panelobj.find('ul.video_element:last');
            var ulVideoElementId = ulVideoElement.attr('id');
            console.log("StreamName: " + ulVideoElementId);

            var videoInfoObj = panelobj.find('div.video_info');

            var progressBarObj = videoInfoObj.find(".progress-bar");
            console.log("ProgressBar - AriaNow: " + progressBarObj.attr('aria-valuenow') + " AriaMax: " + progressBarObj.attr('aria-valuemax'));

            var params = "fname=get_live_videoinfo_filesize&streamName=" + ulVideoElementId;
            jQuery.ajax({
                type: "POST",
                url: "/include/functions.php",
                data: params,
                async: false,
                cache: false,
                success: function(res)
                {
                    if (res !== 0)
                    {
                        var percent = 100 * res / 1024;
                        progressBarObj.css('width', percent+'%').attr('aria-valuenow', res);
                        console.log("ProgressBar - updated aria-valuenow to [" + res + "]");
                        progressBarObj.find('span').text(res + ' MB (MAX 1GB)');

                        if (res <= 500)
                        {
                            progressBarObj.removeClass('progress-bar-danger');
                            progressBarObj.removeClass('progress-bar-warning');  
                            progressBarObj.addClass('progress-bar-info');
                        }
                        else if (res > 500)
                        {
                            progressBarObj.removeClass('progress-bar-info');
                            progressBarObj.removeClass('progress-bar-danger');
                            progressBarObj.addClass('progress-bar-warning');
                        }
                        else if (res > 900)
                        {
                            progressBarObj.removeClass('progress-bar-info');
                            progressBarObj.removeClass('progress-bar-warning');
                            progressBarObj.addClass('progress-bar-danger');

                        }
                    }
                }
            });

        }
    });

};
   
})(jQuery);