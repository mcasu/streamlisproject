(function($){
  
OndemandMp4Loading = function() {
  //var root = $("div.panel-group").find('div.panel-collapse');
  var root = $("div.panel-group");
  //alert("Ciao pippo");
  root.children().each(function( index )
  {
      var panelobj = $(this).find('div.panel-collapse');
      //alert( index + " Pannello con GroupId: " + panelobj.attr('id'));

      if ( panelobj.hasClass("collapse in") )
      {
          ulleft = panelobj.find('ul.left');
          var ulleft_id = ulleft.attr('id');
          //alert("Oggetto ulleft: " + ulleft_id);

          panelobj.find('li.video_list_element').each(function( index )
          {
              var titleobj = $(this).find('div.video_element_title');
              //alert("Element ID [ " + titleobj.attr('id') + " ] - Element CLASS [ " + titleobj.attr('class') + " ]");

              var ulobj = $(this).find('ul');
              var iphoneobj = ulobj.find(".player_iphone:first");
              var videoloadobj = ulobj.find(".video_loading:first");

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
              var fullurl = 'http://' + document.location.hostname + '/mp4/' + ulleft_id + uri;

              $.ajax({
                  url: fullurl,
                  type:'HEAD',
                  error:
                      function(){
                          iphoneobj.removeClass("active");
                          iphoneobj.addClass("notavailable");
                          iphoneobj.hide();
                          videoloadobj.hide();
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
              link_url = "http://" + document.location.hostname + iphone_href;
              //alert("CONTROLLO WEB URL:\nUrl [ " + link_url + " ]");

              $.ajax({
                  url: link_url,
                  type:'HEAD',
                  error:
                      function(){
                          iphoneobj.removeClass("active");
                          iphoneobj.hide();
                          videoloadobj.show();
                          //alert("CONTROLLO WEB:\nUrl [ " + link_url + " ] FAILED.");
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
   
$('.list-group .checked-list-box .list-group-item').each(function () {
        
        // Settings
        var $widget = $(this),
            $checkbox = $('<input type="checkbox" class="hidden" />'),
            color = ($widget.data('color') ? $widget.data('color') : "primary"),
            style = ($widget.data('style') === "button" ? "btn-" : "list-group-item-"),
            settings = {
                on: {
                    icon: 'glyphicon glyphicon-check'
                },
                off: {
                    icon: 'glyphicon glyphicon-unchecked'
                }
            };
            
        $widget.css('cursor', 'pointer');
        $widget.append($checkbox);

        // Event Handlers
        $widget.on('click', function () {
            $checkbox.prop('checked', !$checkbox.is(':checked'));
            $checkbox.triggerHandler('change');
            updateDisplay();
        });
        $checkbox.on('change', function () {
            updateDisplay();
        });
          

        // Actions
        function updateDisplay() {
            var isChecked = $checkbox.is(':checked');

            // Set the button's state
            $widget.data('state', (isChecked) ? "on" : "off");

            // Set the button's icon
            $widget.find('.state-icon')
                .removeClass()
                .addClass('state-icon ' + settings[$widget.data('state')].icon);

            // Update the button's color
            if (isChecked) {
                $widget.addClass(style + color + ' active');
            } else {
                $widget.removeClass(style + color + ' active');
            }
        }

        // Initialization
        function init() {
            
            if ($widget.data('checked') === true) {
                $checkbox.prop('checked', !$checkbox.is(':checked'));
            }
            
            updateDisplay();

            // Inject the icon if applicable
            if ($widget.find('.state-icon').length === 0) {
                $widget.prepend('<span class="state-icon ' + settings[$widget.data('state')].icon + '"></span>');
            }
        }
        init();
    });
    
    $('#get-checked-data').on('click', function(event) {
        event.preventDefault(); 
        var checkedItems = {}, counter = 0;
        $("#check-list-box li.active").each(function(idx, li) {
            checkedItems[counter] = $(li).text();
            counter++;
        });
        $('#display-json').html(JSON.stringify(checkedItems, null, '\t'));
    });
   
   
})(jQuery);