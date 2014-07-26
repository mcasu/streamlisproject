var check_session;
function CheckForSession() {
		var str="chksession=true";
		jQuery.ajax({
				type: "POST",
				url: "/check_session.php",
				data: str,
				cache: false,
				success: function(res){
					if(res == "1")
                                        {
                                            alert('Your session has been expired!');
					}
                                        else
                                        {
                                            //alert('Your session has still alive!');
                                        }
				}
		});
}
check_session = setInterval(CheckForSession, 10000);