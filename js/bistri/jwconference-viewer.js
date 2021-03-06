var peers = new Array();
var room;
var userId;
var username = q(".username").id;
var userrole = q(".userrole").id;
var localStreams = new Array();

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
        userId: username,
        userName: username
    } );

    /* Set events handler */
    
    // bind function "joinConference" to button "Join Conference Room"
    q( "#join" ).addEventListener( "click", joinConference );

    // bind function "quitConference" to button "Quit Conference Room"
    q( "#quit" ).addEventListener( "click", quitConference );
    
    // we register an handler for "onConnected" event.
    BistriConference.signaling.addHandler( "onConnected", function( data )
    {
        //userId = data.id;
        
        // test if the browser is WebRTC compatible
        if ( !BistriConference.isCompatible() ) 
        {
            // if the browser is not compatible, display an alert
            alert( "Il tuo browser non è compatibile con la tecnologia WebRTC.\n" +
                    "Ti consigliamo di usare l'ultima versione disponibile di Chrome o Firefox.");
            return;
        }
        
    } );
    
    // when the user has joined a room
    BistriConference.signaling.addHandler( "onJoinedRoom", function ( data ) 
    {
        // set the current room name
        room = data.room;

        if (data.members.length === 0)
        {
            alert("Non ci sono conferenze attive per l'adunanza selezionata.\nChiedi agli anziani della congregazione selezionata di attivare la conferenza.");
            quitConference();
            return;
        }
        
        console.log( "VIEWER - Hai fatto il join con member name: " + username );
        
        BistriConference.startStream("webcam-hd", function( localStream ){
            
            $("#localStreamsMyVideo").show();
            // display stream into the page
            BistriConference.attachStream( localStream, document.querySelector( "#myvideo" ), 
            { 
                autoplay: true, 
                fullscreen: true, 
                controls: true 
            } );
            
        } );
        
//        var streamNameToView = $( "#streamSelector option:selected" ).val();
//        var appNameToView = $( "#streamSelector option:selected" ).attr("id");
        
        $("#localStreamsPlayer").show();
    });
    
    // when the local user has quitted the room
    BistriConference.signaling.addHandler( "onQuittedRoom", function( ) 
    {
        localStreams = BistriConference.getLocalStreams();
        
        for( var i = 0; i < localStreams.length; i++ )
        {
            // stop and detach the local stream
            BistriConference.stopStream( localStreams[i], function(){
                BistriConference.detachStream(localStreams[i]);
            });
        }
    
        // We stop calls with all conference room members
        BistriConference.endCalls(room);
        
        room = undefined;
    } );
    
    BistriConference.signaling.addHandler( "onIncomingRequest", function ( data ) 
    {
        // display an alert message
       console.log(" VIEWER - Richiesta in entrata per la room [" + data.room + "] dal pid " + data.pid);
    });
    
    // when a new remote stream is received
    BistriConference.streams.addHandler( "onStreamAdded", function ( remoteStream, pid )
    {
        if (userrole !== "2")
        {
            return;
        }
        
        console.log("VIEWER - Aggiungo lo stream di [" + pid + "]");
        // when a remote stream is received we attach it to a node in the page to display it
        
        BistriConference.attachStream( remoteStream, document.querySelector( "#remotevideo" ), { 
            autoplay: true, 
            fullscreen: true,
            controls: true
        } );
    } );    
    
    
    // when an error occured on the server side
    BistriConference.signaling.addHandler( "onError", function ( error ) 
    {
        // display an alert message
        alert( error.text + " (" + error.code + ")" );
    } );
    
    BistriConference.streams.addHandler( "onStreamError", function ( error ) 
    {
        switch( error.name )
        {
            case "PermissionDeniedError":
                alert( "Webcam access has not been allowed");
                break
            case "DevicesNotFoundError":
                alert( "No webcam/mic found on this machine. Process call anyway ?" );
                break
            default:
                alert(error.name);
                break;
        }
        quitConference();
    });
    
    // when an error occurred while trying to join a room
    BistriConference.signaling.addHandler( "onJoinRoomError", function ( error ) {
        // display an alert message
       alert( error.text + " (" + error.code + ")" );
    } );

    // we register an handler for "onPeerJoinedRoom" event, triggered when a remote user join a room
    BistriConference.signaling.addHandler( "onPeerJoinedRoom", function( data )
    {
        console.log( "VIEWER - Il membro " + data.name + " è entrato nella room [" + data.room + "] con pid " + data.pid );
        peers[ data.pid ] = data;
    } );

    // we register an handler for "onPeerQuittedRoom" event, triggered when a remote user quit a room
    BistriConference.signaling.addHandler( "onPeerQuittedRoom", function( data )
    {
        console.log( "Un membro è uscito dalla room: " + data.pid );
        
        if( data.pid in peers )
        {
                delete peers[ data.pid ];
                //isAvailablePeers();
        }
    } );

    // when a local or a remote stream has been stopped
    BistriConference.streams.addHandler( "onStreamClosed", function ( stream ) 
    {
        // remove the stream from the page
        BistriConference.detachStream( stream );
    } );

    function setDataChannelsEvents( channel, pid )
    {
        channel.onOpen = function( event )
        {
                if( !( pid in peers ) ){
                        peers[ pid ] = {};
                }
                peers[ pid ].channel = channel;
                //isAvailablePeers();
        }
        channel.onClosed = function( event )
        {
                if( pid in peers ){
                        delete peers[ pid ].channel;
                }
                //isAvailablePeers();
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
        //input[ channelExists ? "removeAttribute" : "setAttribute" ]( "readonly", true );
        //panel.style.backgroundColor = channelExists ? "#fff" : "#f5f5f5";
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
    var roomToJoin = $('#roomSelector').val();
    $("#localStreamsMyVideo").hide();
    $("#localStreamsPlayer").hide();
    
    if( roomToJoin )
    {
        console.log("VIEWER - Faccio il join alla room [" + roomToJoin + "]");
        // we are ready join the conference room.
        // event "onJoinedRoom" is triggered when the operation successed.
        BistriConference.joinRoom( roomToJoin, 4 );
        
        // Show Quit Conference input button and hide Join Conference input button
        $("#quit").show();
        $("#join").hide();
        $("#panelVideo").show();
    }
    else
    {
        // otherwise, display an alert
        alert( "La room selezionata non è valida!" );
    }
}

// when button "Quit Conference Room" has been clicked
function quitConference()
{
    // quit the current conference room
    BistriConference.quitRoom( room );
    
    // Hide Quit Conference input button and show Join Conference input button
    $("#panelVideo").hide();
    $("#quit").hide();
    $("#join").show();
}

function q( query )
{
    // return the DOM node matching the query
    return document.querySelector( query );
}