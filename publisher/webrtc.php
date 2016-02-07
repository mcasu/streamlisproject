<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>getUserMedia Example</title>
    <meta name="description" content="WebRTC Simple example" />
    <meta name="author" content="Ido Green | greenido.wordpress.com">

    <meta name="keywords" content="WebRTC, HTML5, JavaScript, Hack, Ido Green" />
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1">
    <meta http-equiv="X-UA-Compatible" content="chrome=1" />
    <base target="_blank">

    <style>
      video {
        height: 25em;
        position: relative;
        left: 15%;
      }
      #video-space {
        padding: 1em;
        background-color: rgba(70, 70, 6, 0.55);
        border-radius: 25px;
      }
      ul {
        padding: 2em;
        font-family: sans-serif;
        font-size: 120%;
        line-height:160%;
        background-color: lightgray;
        border-radius: 25px
      }
    </style>
  </head>

  <body>

    <h1>getUserMedia API Example</h1>

    <div id='video-space'>
      <video autoplay></video>
    </div>

    <script>
//      navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
//      
//    var constraints = {audio: false, video: true};
//      var video = document.querySelector("video");
//      function successCallback(stream) {
//        // stream available to console so you could inspect it and see what this object looks like
//        window.stream = stream;
//        if (window.URL) {
//          video.src = window.URL.createObjectURL(stream);
//        } else {
//          video.src = stream;
//        }
//        video.play();
//      }
//      function errorCallback(error) {
//        console.log("navigator.getUserMedia error: ", error);
//      }
//      navigator.getUserMedia(constraints, successCallback, errorCallback);

        var p = navigator.mediaDevices.getUserMedia({ 
            audio: true, 
            video: { frameRate: { ideal: 25, max: 30 }, width: 576, height: 720, aspectRatio: 1.33 } });

        p.then(function(mediaStream) {
          var video = document.querySelector('video');
          video.src = window.URL.createObjectURL(mediaStream);
          video.onloadedmetadata = function(e) {
            // Do something with the video here.
          };
        });

        p.catch(function(err) { console.log(err.name); }); // always check for errors at the end.
    </script>

</body>
</html>