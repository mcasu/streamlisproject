var room;

// when Bistri API client is ready, function
// "onBistriConferenceReady" is invoked
onBistriConferenceReady = function () {

    // test if the browser is WebRTC compatible
    if ( !BistriConference.isCompatible() ) {
        // if the browser is not compatible, display an alert
        alert( "your browser is not WebRTC compatible !" );
        // then stop the script execution
        return;
    }

    // initialize API client with application keys
    // if you don't have your own, you can get them at:
    // https://api.developers.bistri.com/login
    BistriConference.init( {
        appId: "435f88e5",
        appKey: "b0560d8d190b7c67629c336399406afe",
        debug: true
    } );

    /* Set events handler */
    
    // bind function "joinConference" to button "Join Conference Room"
    q( "#join" ).addEventListener( "click", joinConference );

    // bind function "quitConference" to button "Quit Conference Room"
    q( "#quit" ).addEventListener( "click", quitConference );
    
    
    // when the user has joined a room
    BistriConference.signaling.addHandler( "onJoinedRoom", function ( data ) {
        // set the current room name
        room = data.room;
        // ask the user to access to his webcam
        BistriConference.startStream( "webcamSD", function( localStream ){
            // when webcam access has been granted
            // show panel with video containers
            $("#panelVideo").show();
            
            // insert the local webcam stream into div#video_container node
            BistriConference.attachStream( localStream, q( "#myvideo" ) );
            
            // then, for every single members present in the room ...
            for ( var i=0, max=data.members.length; i<max; i++ )
            {
                alert("Chiamo ID: " + data.members[i].id + " nella room: " + data.room);
                // ... request a call
                BistriConference.call( data.members[i].id, data.room );
            }
        } );
    } );


    // when an error occured on the server side
    BistriConference.signaling.addHandler( "onError", function ( error ) {
        // display an alert message
        alert( error.text + " (" + error.code + ")" );
    } );


    BistriConference.signaling.addHandler( "onIncomingRequest", function ( request ) {
        // display an alert message
       alert(request);
    } );
        
    // when an error occurred while trying to join a room
    BistriConference.signaling.addHandler( "onJoinRoomError", function ( error ) {
        // display an alert message
       alert( error.text + " (" + error.code + ")" );
    } );

    // when the local user has quitted the room
    BistriConference.signaling.addHandler( "onQuittedRoom", function( ) 
    {
        // stop the local stream
        BistriConference.stopStream();
    } );

    // when a new remote stream is received
    BistriConference.streams.addHandler( "onStreamAdded", function ( remoteStream, pid )
    {
        // when a remote stream is received we attach it to a node in the page to display it
	var nodes = document.querySelectorAll( "#remoteStreams" );
        
        for(var i=0;  i < nodes.length; i++ )
        {
            if( !nodes[ i ].firstChild )
            {
                if( peers[ pid ] )
                {
                        peers[ pid ].name = "peer " + ( i + 1 );
                }
                alert("Insert new remote stream into div: " + nodes[ i ].attr('id'));
                BistriConference.attachStream( remoteStream, nodes[ i ], { autoplay: true, fullscreen: true } );
                break;
            }
        }
    } );

    // when a local or a remote stream has been stopped
    BistriConference.streams.addHandler( "onStreamClosed", function ( stream ) {
        // remove the stream from the page
        BistriConference.detachStream( stream );
    } );

    // open a new session on the server
    BistriConference.connect();
};

// when button "Join Conference Room" has been clicked
function joinConference()
{
    //var roomToJoin = q( "#room_field" ).value;
    
    var roomToJoin = $( "#roomSelector option:selected" ).value();
    alert("Join to room: " + roomToJoin);
    
    // if "Conference Name" field is not empty ...
    if( roomToJoin )
    {
        // ... join the room
        BistriConference.joinRoom( roomToJoin, 3 );
        
        // Show Quit Conference input button and hide Join Conference input button
        $("#quit").show();
        $("join").hide();
    }
    else
    {
        // otherwise, display an alert
        alert( "You must enter a room name!" );
    }
};

// when button "Quit Conference Room" has been clicked
function quitConference()
{
    // quit the current conference room
    BistriConference.quitRoom( room );
    
    // Hide Quit Conference input button and show Join Conference input button
    $("#quit").hide();
    $("join").show();
};

function q( query )
{
    // return the DOM node matching the query
    return document.querySelector( query );
};