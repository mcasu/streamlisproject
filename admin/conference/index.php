<html><head>
<meta charset="UTF-8">
<link href="../../style/demo.css" rel="stylesheet">
<link rel="chrome-webstore-item" href="https://chrome.google.com/webstore/detail/paoaiaehoigfjoojpeababegjiijdoan"></head>
<body>

	<div class="stripes" style="display: none;"></div>
	
	<div class="conference">

		<table>
			<tr>
				<td>
					<div class="local-stream" id="video-5"></div>
				</td>
			</tr>
			
			<tr>
				<td>
					<div class="remote-stream" id="video-1"></div>
				</td>
				<td>
					<div class="remote-stream" id="video-2"></div>
				</td>
				<td>
					<div class="remote-stream" id="video-3"></div>
				</td>
				<td>
					<div class="remote-stream" id="video-4"></div>
				</td>
			</tr>
			
		</table>


		<div class="control" data-bind="visible: joinedRoom">
			<!--div class="local-stream"></div-->
			<pre class="conference-link">Copia il seguente link e mandalo ai tuoi amici per iniziare una conferenza:<textarea data-bind="click: selectContent"></textarea></pre>

			<div class="chat">
				<div class="messages" style="background-color: rgb(255, 255, 255);">
					<!-- ko foreach: messages -->
					<div data-bind="text: $data"></div>
					<!-- /ko -->
				</div>
				<form data-bind="submit: sendMessage">
					<input type="text" class="message-field" data-bind="value: message">
					<input type="submit" value="send" class="btn">
				</form>
			</div>

			<input type="button" value="Quit Conference" class="btn btn-danger" data-bind="click: quitConference">

		</div>
		

		<div class="compatibility" data-bind="visible: !isCompatible()" style="display: none;">
			<div>Sorry your browser is not compatible.<br>We invite you to use a webRTC enabled browser:
				<a href="http://www.mozilla.org/en-US/firefox/new/" target="_blank">Firefox 22+</a>, <a href="https://www.google.com/intl/en/chrome/browser/" target="_blank">Chrome 23+</a> or <a href="http://www.opera.com/fr/" target="_blank">Opera 18+</a><br>
				or <a data-bind="attr: { href: pluginURL }" target="_blank" href="http://bit.ly/WebRTCpluginPC">download Temasys WebRTC plugin for Internet Explorer and Safari</a>.
			</div>
		</div>

		<div class="connecting" data-bind="visible: !connected() &amp;&amp; isCompatible()" style="display: none;">
			<div>connecting ...</div>
		</div>

		<div class="device-selector" data-bind="visible: isCompatible() &amp;&amp; connected() &amp;&amp; !joinedRoom()" style="display: none;">
			<div>
				<!--p><input type="button" value="Use Audio" data-bind="click: startAudio" class="btn btn-info btn-large"/></p-->
				<p><input type="button" value="Use SD webcam" data-bind="click: startWebcamSD" class="btn btn-info btn-large"></p>
				<p><input type="button" value="Use HD webcam" data-bind="click: startWebcamHD" class="btn btn-info btn-large"></p>
			</div>
		</div>

	</div>

	<script type="text/javascript" src="https://ajax.aspnetcdn.com/ajax/knockout/knockout-2.2.1.js"></script>
	<script type="text/javascript" src="https://api.bistri.com/bistri.conference.min.js?v=3"></script>
	<script type="text/javascript" src="../../js/bistri/api-demo.js"></script>
	<script type="text/javascript" src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
	<script type="text/javascript" src="https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/js/bootstrap.min.js"></script>


</body></html>