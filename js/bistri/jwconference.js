var peers = {};
var room;
var userId;

// when Bistri API client is ready, function
// "onBistriConferenceReady" is invoked
var onBistriConferenceReady = function () 
{
    // initialize API client with application keys
    // if you don't have your own, you can get them at:
    // https://api.developers.bistri.com/login
    BistriConference.init( {
        appId: "435f88e5",
        appKey: "b0560d8d190b7c67629c336399406afe",
        debug: true,
        userName: "Bistri User"
    } );

    /* Set events handler */
    
    // bind function "joinConference" to button "Join Conference Room"
    q( "#join" ).addEventListener( "click", joinConference );

    // bind function "quitConference" to button "Quit Conference Room"
    q( "#quit" ).addEventListener( "click", quitConference );
    
    // we register an handler for "onConnected" event.
    BistriConference.signaling.addHandler( "onConnected", function( data )
    {
        userId = data.id;
        
        // test if the browser is WebRTC compatible
        if ( !BistriConference.isCompatible() ) {
            // if the browser is not compatible, display an alert
            alert( "Your browser is not WebRTC compatible!" );
            // then stop the script execution
            return;
        }
    } );
    
    // when the user has joined a room
    BistriConference.signaling.addHandler( "onJoinedRoom", function ( data ) 
    {
        // set the current room name
        room = data.room;

        // once user has successfully joined the room we start a call and open a data channel with every single room members
        for( var i = 0; i < data.members.length; i++ )
        {
            console.log( "Hai fatto il join con member id: ", data.members[ i ].id, "member display name:", data.members[ i ].name );
            
            peers[ data.members[ i ].id ] = data.members[ i ];
            // send a call request to peer
            BistriConference.call( data.members[ i ].id, data.room );
            // send data channel request to peer
            BistriConference.openDataChannel( data.members[ i ].id, "myChannel", data.room, { reliable: true } );
        }
    });
    
    // when the local user has quitted the room
    BistriConference.signaling.addHandler( "onQuittedRoom", function( ) 
    {
        room = undefined;
        // stop the local stream
        BistriConference.stopStream();
    } );
    
    // when an error occured on the server side
    BistriConference.signaling.addHandler( "onError", function ( error ) 
    {
        // display an alert message
        alert( error.text + " (" + error.code + ")" );
    } );
        
    // when an error occurred while trying to join a room
    BistriConference.signaling.addHandler( "onJoinRoomError", function ( error ) {
        // display an alert message
       alert( error.text + " (" + error.code + ")" );
    } );

    // we register an handler for "onPeerJoinedRoom" event, triggered when a remote user join a room
    BistriConference.signaling.addHandler( "onPeerJoinedRoom", function( data )
    {
        console.log( "Un membro è entrato nella room: " + data.pid );
        //peers[ data.pid ] = data;
    } );

    // we register an handler for "onPeerQuittedRoom" event, triggered when a remote user quit a room
    BistriConference.signaling.addHandler( "onPeerQuittedRoom", function( data )
    {
        console.log( "Un membro è uscito dalla room: " + data.pid );
        
        if( data.pid in peers )
        {
                delete peers[ data.pid ];
                isAvailablePeers();
        }
    } );

    // when a local or a remote stream has been stopped
    BistriConference.streams.addHandler( "onStreamClosed", function ( stream ) 
    {
        // remove the stream from the page
        BistriConference.detachStream( stream );
    } );

    BistriConference.signaling.addHandler( "onIncomingRequest", function ( data ) 
    {
        // display an alert message
       console.log("Richiesta in entrata: " + data);
       
       BistriConference.startStream( "640x480", function( remoteStream )
       {
                var roomId = data.room;

                // when the local stream is received we attach it to a node in the page to display it
                BistriConference.attachStream( remoteStream, document.querySelector( "#remoteStreams" ), { autoplay: true, fullscreen: true } );

                // when the local stream has been started and attached to the page
                // we are ready join the conference room.
                // event "onJoinedRoom" is triggered when the operation successed.
                BistriConference.joinRoom( roomId, 3 );
        });
    });

    // when a new remote stream is received
    BistriConference.streams.addHandler( "onStreamAdded", function ( remoteStream )
    {
        console.log("Aggiungo un nuovo stream...");
        // when a remote stream is received we attach it to a node in the page to display it
	var nodes = $( ".remoteStreams" );
        console.log("Remote streams div numbers: " + nodes.length);
        
        for(var i=0;  i < nodes.length; i++ )
        {    
            console.log("Nodo id: " + nodes[ i ].attr('id'));
            if( !nodes[ i ].firstChild )
            {
                if( peers[ pid ] )
                {
                    peers[ pid ].name = "peer " + ( i + 1 );
                }

                console.log("Insert new remote stream into div: " + nodes[ i ].attr('id'));
                BistriConference.attachStream( remoteStream, nodes[ i ], { autoplay: true, fullscreen: true } );
                break;
            }
        }
    } );

    function setDataChannelsEvents( channel, pid )
    {
        channel.onOpen = function( event )
        {
                if( !( pid in peers ) ){
                        peers[ pid ] = {};
                }
                peers[ pid ].channel = channel;
                isAvailablePeers();
        }
        channel.onClosed = function( event )
        {
                if( pid in peers ){
                        delete peers[ pid ].channel;
                }
                isAvailablePeers();
        }
        channel.onMessage = function( event )
        {
                window.displayMessage( peers[ pid ].name + " > " + event.data );
        }
    }

    function isAvailablePeers()
    {
        var channelExists = false;
        var input = document.querySelector( ".message-field" );
        var panel = document.querySelector( ".messages" );
        for( peer in peers )
        {
                if( peers[ peer ][ "channel" ] )
                {
                        channelExists = true;
                }
        }
        input[ channelExists ? "removeAttribute" : "setAttribute" ]( "readonly", true );
        panel.style.backgroundColor = channelExists ? "#fff" : "#f5f5f5";
    }

    window.displayMessage = function( message )
    {
        var panel = document.querySelector( ".messages" );
        viewModel.messages.push( message );
        panel.scrollTop = panel.scrollHeight;
    };

    // we register an handler for "onDataChannelCreated" event.
    BistriConference.channels.addHandler( "onDataChannelCreated", setDataChannelsEvents );

    // we register an handler for "onDataChannelRequested" event.
    BistriConference.channels.addHandler( "onDataChannelRequested", setDataChannelsEvents );

    // open a new session on the server
    BistriConference.connect();
};

// when button "Join Conference Room" has been clicked
function joinConference()
{
    var roomToJoin = $( "#roomSelector option:selected" ).val();
    alert("Join to room: " + roomToJoin);
    
    // if "Conference Name" field is not empty ...
    if( roomToJoin )
    {
        BistriConference.startStream("640x480", function( localStream )
        {
            // when the local stream is received we attach it to a node in the page to display it
            BistriConference.attachStream( localStream, document.querySelector( "#myvideo" ), { autoplay: true, fullscreen: true } );

            // when the local stream has been started and attached to the page
            // we are ready join the conference room.
            // event "onJoinedRoom" is triggered when the operation successed.
            BistriConference.joinRoom( roomToJoin, 3 );
        } );
        // Show Quit Conference input button and hide Join Conference input button
        $("#quit").show();
        $("#join").hide();
        $("#panelVideo").show();
    }
    else
    {
        // otherwise, display an alert
        alert( "You must enter a room name!" );
    }
}

// when button "Quit Conference Room" has been clicked
function quitConference()
{
    // quit the current conference room
    BistriConference.quitRoom( room );
    
    // Hide Quit Conference input button and show Join Conference input button
    $("#quit").hide();
    $("#join").show();
}

function q( query )
{
    // return the DOM node matching the query
    return document.querySelector( query );
}