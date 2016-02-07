'use strict';

var peers = new Array();
var room;
var userId;
var username = q(".username").id;
var userrole = q(".userrole").id;

var videoElement = document.querySelector('#myvideo');
var videoSelect = document.querySelector('select#videoSource');
var selectors = [videoSelect];
var getUserMedia = null;
var attachMediaStream = null;
var reattachMediaStream = null;
var webrtcDetectedBrowser = null;
var webrtcDetectedVersion = null;
var webrtcMinimumVersion = null;

// Returns the result of getUserMedia as a Promise.
function requestUserMedia(constraints) {
  return new Promise(function(resolve, reject) {
    getUserMedia(constraints, resolve, reject);
  });
}

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
        if ( !BistriConference.isCompatible() ) {
            // if the browser is not compatible, display an alert
            alert( "Il tuo browser non è compatibile con la tecnologia WebRTC.\n" +
                    "Ti consigliamo di usare l'ultima versione disponibile di Chrome o Firefox.");
            return;
        }
    } );
    
    
    function gotDevices(deviceInfos) 
    {
        // Handles being called several times to update labels. Preserve values.
        var values = selectors.map(function(select) 
        {
          return select.value;
        });
        
        selectors.forEach(function(select) 
        {
          while (select.firstChild) 
          {
            select.removeChild(select.firstChild);
          }
        });
        
        for (var i = 0; i !== deviceInfos.length; ++i) 
        {
            var deviceInfo = deviceInfos[i];
            var option = document.createElement('option');
            option.value = deviceInfo.deviceId;

            if (deviceInfo.kind === 'videoinput') {
              option.text = deviceInfo.label || 'camera ' + (videoSelect.length + 1);
              videoSelect.appendChild(option);
            } 
            else 
            {
              console.log('Some other kind of source/device: ', deviceInfo);
            }
        }
        
        selectors.forEach(function(select, selectorIndex) 
        {
          if (Array.prototype.slice.call(select.childNodes).some(function(n) {
                return n.value === values[selectorIndex];
              })) 
          {
            select.value = values[selectorIndex];
          }
        });
      }
    
    function errorCallback(error) {
        console.log('navigator.getUserMedia error: ', error);
      }
      
    function start() {
      if (window.stream) {
        window.stream.getTracks().forEach(function(track) {
          track.stop();
        });
      }
      //var audioSource = audioInputSelect.value;
      var videoSource = videoSelect.value;
      var constraints = {
        audio: false,
        video: {deviceId: videoSource ? {exact: videoSource} : undefined}
      };
      navigator.mediaDevices.getUserMedia(constraints)
      .then(function(stream) {
        window.stream = stream; // make stream available to console
        videoElement.srcObject = stream;
        // Refresh button list in case labels have become available
        return navigator.mediaDevices.enumerateDevices();
      })
      .then(gotDevices)
      .catch(errorCallback);
    }      
      
    // when the user has joined a room
    BistriConference.signaling.addHandler( "onJoinedRoom", function ( data ) 
    {
        // set the current room name
        room = data.room;
        console.log( "PUBLISHER - Hai fatto il join con member name: " + username );
        
        $("#joined_user_number").find(".label").html(data.members.length);
        
        if (typeof window === 'object') 
        {
            if (window.HTMLMediaElement &&
              !('srcObject' in window.HTMLMediaElement.prototype)) {
              // Shim the srcObject property, once, when HTMLMediaElement is found.
              Object.defineProperty(window.HTMLMediaElement.prototype, 'srcObject', {
                get: function() {
                  // If prefixed srcObject property exists, return it.
                  // Otherwise use the shimmed property, _srcObject
                  return 'mozSrcObject' in this ? this.mozSrcObject : this._srcObject;
                },
                set: function(stream) {
                  if ('mozSrcObject' in this) {
                    this.mozSrcObject = stream;
                  } else {
                    // Use _srcObject as a private property for this shim
                    this._srcObject = stream;
                    // TODO: revokeObjectUrl(this.src) when !stream to release resources?
                    this.src = URL.createObjectURL(stream);
                  }
                }
              });
            }
            // Proxy existing globals
            getUserMedia = window.navigator && window.navigator.getUserMedia;
          }

        // Attach a media stream to an element.
        attachMediaStream = function(element, stream) {
          element.srcObject = stream;
        };

        reattachMediaStream = function(to, from) {
          to.srcObject = from.srcObject;
        };

        if (navigator.mozGetUserMedia) 
        {
            console.log('This appears to be Firefox');
            
            // getUserMedia constraints shim.
            getUserMedia = function(constraints, onSuccess, onError) {
              var constraintsToFF37 = function(c) {
                if (typeof c !== 'object' || c.require) {
                  return c;
                }
                var require = [];
                Object.keys(c).forEach(function(key) {
                  if (key === 'require' || key === 'advanced' || key === 'mediaSource') {
                    return;
                  }
                  var r = c[key] = (typeof c[key] === 'object') ?
                      c[key] : {ideal: c[key]};
                  if (r.min !== undefined ||
                      r.max !== undefined || r.exact !== undefined) {
                    require.push(key);
                  }
                  if (r.exact !== undefined) {
                    if (typeof r.exact === 'number') {
                      r.min = r.max = r.exact;
                    } else {
                      c[key] = r.exact;
                    }
                    delete r.exact;
                  }
                  if (r.ideal !== undefined) {
                    c.advanced = c.advanced || [];
                    var oc = {};
                    if (typeof r.ideal === 'number') {
                      oc[key] = {min: r.ideal, max: r.ideal};
                    } else {
                      oc[key] = r.ideal;
                    }
                    c.advanced.push(oc);
                    delete r.ideal;
                    if (!Object.keys(r).length) {
                      delete c[key];
                    }
                  }
                });
                if (require.length) {
                  c.require = require;
                }
                return c;
              };
              if (webrtcDetectedVersion < 38) {
                console.log('spec: ' + JSON.stringify(constraints));
                if (constraints.audio) {
                  constraints.audio = constraintsToFF37(constraints.audio);
                }
                if (constraints.video) {
                  constraints.video = constraintsToFF37(constraints.video);
                }
                console.log('ff37: ' + JSON.stringify(constraints));
              }
              return navigator.mozGetUserMedia(constraints, onSuccess, onError);
            };

            navigator.getUserMedia = getUserMedia;

            // Shim for mediaDevices on older versions.
            if (!navigator.mediaDevices) {
              navigator.mediaDevices = {getUserMedia: requestUserMedia,
                addEventListener: function() { },
                removeEventListener: function() { }
              };
            }
            navigator.mediaDevices.enumerateDevices =
                navigator.mediaDevices.enumerateDevices || function() {
              return new Promise(function(resolve) {
                var infos = [
                  {kind: 'audioinput', deviceId: 'default', label: '', groupId: ''},
                  {kind: 'videoinput', deviceId: 'default', label: '', groupId: ''}
                ];
                resolve(infos);
              });
            };
        }
        else if (navigator.webkitGetUserMedia && window.webkitRTCPeerConnection) 
        {
            console.log('This appears to be Chrome');
            
            // getUserMedia constraints shim.
            var constraintsToChrome = function(c) {
              if (typeof c !== 'object' || c.mandatory || c.optional) {
                return c;
              }
              var cc = {};
              Object.keys(c).forEach(function(key) {
                if (key === 'require' || key === 'advanced' || key === 'mediaSource') {
                  return;
                }
                var r = (typeof c[key] === 'object') ? c[key] : {ideal: c[key]};
                if (r.exact !== undefined && typeof r.exact === 'number') {
                  r.min = r.max = r.exact;
                }
                var oldname = function(prefix, name) {
                  if (prefix) {
                    return prefix + name.charAt(0).toUpperCase() + name.slice(1);
                  }
                  return (name === 'deviceId') ? 'sourceId' : name;
                };
                if (r.ideal !== undefined) {
                  cc.optional = cc.optional || [];
                  var oc = {};
                  if (typeof r.ideal === 'number') {
                    oc[oldname('min', key)] = r.ideal;
                    cc.optional.push(oc);
                    oc = {};
                    oc[oldname('max', key)] = r.ideal;
                    cc.optional.push(oc);
                  } else {
                    oc[oldname('', key)] = r.ideal;
                    cc.optional.push(oc);
                  }
                }
                if (r.exact !== undefined && typeof r.exact !== 'number') {
                  cc.mandatory = cc.mandatory || {};
                  cc.mandatory[oldname('', key)] = r.exact;
                } else {
                  ['min', 'max'].forEach(function(mix) {
                    if (r[mix] !== undefined) {
                      cc.mandatory = cc.mandatory || {};
                      cc.mandatory[oldname(mix, key)] = r[mix];
                    }
                  });
                }
              });
              if (c.advanced) {
                cc.optional = (cc.optional || []).concat(c.advanced);
              }
              return cc;
            };

            getUserMedia = function(constraints, onSuccess, onError) {
              if (constraints.audio) {
                constraints.audio = constraintsToChrome(constraints.audio);
              }
              if (constraints.video) {
                constraints.video = constraintsToChrome(constraints.video);
              }
              console.log('chrome: ' + JSON.stringify(constraints));
              return navigator.webkitGetUserMedia(constraints, onSuccess, onError);
            };
            navigator.getUserMedia = getUserMedia;

            if (!navigator.mediaDevices) {
              navigator.mediaDevices = {getUserMedia: requestUserMedia,
                                        enumerateDevices: function() {
                return new Promise(function(resolve) {
                  var kinds = {audio: 'audioinput', video: 'videoinput'};
                  return MediaStreamTrack.getSources(function(devices) {
                    resolve(devices.map(function(device) {
                      return {label: device.label,
                              kind: kinds[device.kind],
                              deviceId: device.id,
                              groupId: ''};
                    }));
                  });
                });
              }};
            }

            // A shim for getUserMedia method on the mediaDevices object.
            // TODO(KaptenJansson) remove once implemented in Chrome stable.
            if (!navigator.mediaDevices.getUserMedia) {
              navigator.mediaDevices.getUserMedia = function(constraints) {
                return requestUserMedia(constraints);
              };
            } else {
              // Even though Chrome 45 has navigator.mediaDevices and a getUserMedia
              // function which returns a Promise, it does not accept spec-style
              // constraints.
              var origGetUserMedia = navigator.mediaDevices.getUserMedia.
                  bind(navigator.mediaDevices);
              navigator.mediaDevices.getUserMedia = function(c) {
                console.log('spec:   ' + JSON.stringify(c)); // whitespace for alignment
                c.audio = constraintsToChrome(c.audio);
                c.video = constraintsToChrome(c.video);
                console.log('chrome: ' + JSON.stringify(c));
                return origGetUserMedia(c);
              };
            }
            
        }
        
        navigator.mediaDevices.enumerateDevices()
        .then(gotDevices)
        .catch(errorCallback);
        
        videoSelect.onchange = start;

        start();
        
        // we start a call and open a data channel with every single room members
        for( var i = 0; i < data.members.length; i++ )
        {
            peers[ data.members[ i ].id ] = data.members[ i ];
            // send a call request to peer
            BistriConference.call( data.members[ i ].id, data.room, { "stream": window.stream } );
        }
        
//        BistriConference.startStream("720x576:25", function( localStream ){
//
//        // display stream into the page
//        BistriConference.attachStream( localStream, document.querySelector( "#myvideo" ), 
//        { autoplay: true, fullscreen: true, controls: true } );
//
//        // we start a call and open a data channel with every single room members
//        for( var i = 0; i < data.members.length; i++ )
//        {
//            peers[ data.members[ i ].id ] = data.members[ i ];
//            // send a call request to peer
//            BistriConference.call( data.members[ i ].id, data.room, { "stream": localStream } );
//        }
//    } );
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
    // we register an handler for "onPeerJoinedRoom" event, triggered when a remote user join a room
    BistriConference.signaling.addHandler( "onPeerJoinedRoom", function( data )
    {
        console.log( "PUBLISHER - Il membro " + data.name + " è entrato nella room [" + data.room + "] con pid " + data.pid );
        
        peers[ data.pid ] = data;
        var num = $(BistriConference.getRoomMembers(data.room)).length;
        console.log( "PUBLISHER - Adesso i membri sono [" + num + "]");
        $("#joined_user_number").find(".label").html(num);
        
        // send a call request to peer
        BistriConference.call( data.pid, data.room, { "stream": window.stream } );
    } );

    // we register an handler for "onPeerQuittedRoom" event, triggered when a remote user quit a room
    BistriConference.signaling.addHandler( "onPeerQuittedRoom", function( data )
    {
        console.log( "PUBLISHER - Un membro è uscito dalla room: " + data.pid );
        
        if( data.pid in peers )
        {
                delete peers[ data.pid ];
                //isAvailablePeers();
        }
        
        var num = $(BistriConference.getRoomMembers(data.room)).length;
        $("#joined_user_number").find(".label").html(num);
    } );

    // when a local or a remote stream has been stopped
    BistriConference.streams.addHandler( "onStreamClosed", function ( remoteStream ) 
    {
        console.log("PUBLISHER - Rimuovo lo stream...");
        // remove the stream from the page
        BistriConference.detachStream( remoteStream );
    } );

    BistriConference.signaling.addHandler( "onIncomingRequest", function ( data ) 
    {
        // display an alert message
       console.log("PUBLISHER - Richiesta in entrata: " + data);
       
//       BistriConference.startStream( "640x480", function( remoteStream )
//       {
//                var roomId = data.room;
//
//                // when the local stream is received we attach it to a node in the page to display it
//                BistriConference.attachStream( remoteStream, document.querySelector( "#remoteStreams" ), { autoplay: true, fullscreen: true } );
//
//                // when the local stream has been started and attached to the page
//                // we are ready join the conference room.
//                // event "onJoinedRoom" is triggered when the operation successed.
//                BistriConference.joinRoom( roomId, 4 );
//        });
    });

    // when a new remote stream is received
    BistriConference.streams.addHandler( "onStreamAdded", function ( remoteStream, pid )
    {
        if (userrole === "2")
        {
            return;
        }
        console.log("PUBLISHER - Aggiungo lo stream di [" + pid + "]");
        // when a remote stream is received we attach it to a node in the page to display it
	var nodes = $( ".remoteStreams" );
        console.log("PUBLISHER - Remote streams div numbers: " + nodes.length);
        
        for(var i=0;  i < nodes.length; i++ )
        {    
            //console.log("Nodo id: " + nodes[ i ].attr('id'));
            if( !nodes[ i ].firstChild )
            {
                if( peers[ pid ] )
                {
                    peers[ pid ].name = "peer " + ( i + 1 );
                }

                $(nodes[ i ]).parent().parent().find("h5").html(pid);
                
                BistriConference.attachStream( remoteStream, nodes[ i ], { autoplay: true, fullscreen: false } );
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
//        input[ channelExists ? "removeAttribute" : "setAttribute" ]( "readonly", true );
//        panel.style.backgroundColor = channelExists ? "#fff" : "#f5f5f5";
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
    var roomToJoin = $('.group_publishcode').attr('id');
    //alert("Join to room: " + roomToJoin);
    
    // if "Conference Name" field is not empty ...
    if( roomToJoin )
    {
        // we are ready join the conference room.
        // event "onJoinedRoom" is triggered when the operation successed.
        BistriConference.joinRoom( roomToJoin, 4 );
        
        // Show Quit Conference input button and hide Join Conference input button
        $("#quit").show();
        $("#join").hide();
        $("#panelVideo").show();
        $("#joined_user_number").show();
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
    $("#joined_user_number").hide();
    $("#panelVideo").hide();
    $("#quit").hide();
    $("#join").show();
}

function q( query )
{
    // return the DOM node matching the query
    return document.querySelector( query );
}