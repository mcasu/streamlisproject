/**
 * @class Gab
 * @classDesc Network module for WooGeen P2P video chat
 */
function Gab(serverAddress, token, chatId) {

  var self = this;
  var xmppServer = null;
  var serverDomain;

  // Event handlers.
  /**
   * @property {function} onConnected
   * @memberOf Gab#
   */
  this.onConnected = null;
  /**
   * @property {function} onDisconnect
   * @memberOf Gab#
   */
  this.onDisconnected = null;
  /**
   * @property {function} onConnectFailed This function will be executed after
   *           connect to server failed. Parameter: errorCode for error code.
   * @memberOf Gab#
   */
  this.onConnectFailed = null;
  /**
   * @property {function} onVideoInvitation Parameter: senderId for sender's ID.
   * @memberOf Gab#
   */
  this.onVideoInvitation = null;
  /**
   * @property {function} onVideoDenied Parameter: senderId for sender's ID.
   * @memberOf Gab#
   */
  this.onVideoDenied = null;
  /**
   * @property {function} onVideoStopped Parameter: senderId for sender's ID.
   * @memberOf Gab#
   */
  this.onVideoStopped = null;
  /**
   * @property {function} onVideoAccepted Parameter: senderId for sender's ID.
   * @memberOf Gab#
   */
  this.onVideoAccepted = null;
  /**
   * @property {function} onVideoError Parameter: errorCode.
   * @memberOf Gab#
   */
  this.onVideoError = null;
  /**
   * @property {function} onVideoSignal Parameter: senderId, signaling message.
   * @memberOf Gab#
   */
  this.onVideoSignal = null;
  /**
   * @property {function} onChatReady Parameter: a list of uid in current chat
   * @memberOf Gab#
   */
  this.onChatReady = null;
  /**
   * @property {function} onChatWait
   * @memberOf Gab#
   */
  this.onChatWait = null;

  /**
   * @property {function} onAuthenticated
   * @memberOf Gab#
   */
  this.onAuthenticated = null;

  /**
   * Connect to the signaling server
   *
   * @memberOf Gab#
   * @param {string}
   *          uid Current user's ID.
   * @param {string}
   *          token Token for authentication.
   */
  var connect = function(serverAddress, token) {
    var data = JSON.parse(token);
    var jid = data['jid'];
    var pw = data['password'];
    serverDomain = Strophe.getDomainFromJid(jid);

    var on_video_message = function(message) {
      var full_jid = message.attributes['from'].value;
      var jid = Strophe.getBareJidFromJid(full_jid);
      console.info(jid);
      var msgType = message.firstChild.attributes['type'].value;
      if (msgType == 'video-invitation') {
        console.info('Received a video invitation.');
        if (self.onVideoInvitation)
          self.onVideoInvitation(jid);
        return true;
      }
      ;

      if (msgType == 'video-denied') {
        console.info('Remote user denied your invitation.');
        if (self.onVideoDenied)
          self.onVideoDenied(jid);
        return true;
      }
      ;

      if (msgType == 'video-closed') {
        console.info('Remote user stopped video chat.');
        if (self.onVideoStopped)
          self.onVideoStopped(jid);
        return true;
      }
      ;

      if (msgType == 'video-accepted') {
        console.info('Remote user agreed your invitation.');
        if (self.onVideoAccepted)
          self.onVideoAccepted(jid);
        return true;
      }
      ;

      if (msgType == 'video-signal') {
        console.log('Received signal message');
        if (self.onVideoSignal)
          self.onVideoSignal(jid, JSON
              .parse(message.firstChild.attributes['data'].value));
        return true;
      }
      ;

      if (msgType == 'video-stopped') {
        console.log('Remote user stopped video chat.');
        if (self.onVideoStopped)
          self.onVideoStopped(jid);
        return true;
      }
      ;

    }

    var on_message = function(message) {
      // video handshake information
      if (message.firstChild.tagName == 'video-chat') {
        on_video_message(message);
        return true;
      }
    }
    console.info(jid + " " + pw);
    xmppServer = new Strophe.Connection(serverAddress);
    xmppServer.connect(jid, pw, function(status) {
      switch(status){
      case Strophe.Status.ERROR:
        console.error("XMPP connection error.");
        if (self.onConnectFailed)
          self.onConnectFailed(parseInt(status));
        break;
      case Strophe.Status.CONNECTING:
        console.info("XMPP connecting.");
        break;
      case Strophe.Status.CONNFAIL:
        console.error("XMPP connection fail.");
        if (self.onConnectFailed)
          self.onConnectFailed(parseInt(status));
        break;
      case Strophe.Status.AUTHENTICATING:
        console.info("XMPP authenticating.");
        break;
      case Strophe.Status.AUTHFAIL:
        console.info("XMPP authentication fail.");
        if (self.onConnectFailed)
          self.onConnectFailed(parseInt(status));
        break;
      case Strophe.Status.CONNECTED:
        console.info("Connected to XMPP server.");
        if (self.onConnected) self.onConnected();
        if (self.onAuthenticated) self.onAuthenticated(Strophe.getBareJidFromJid(xmppServer.jid))
        xmppServer.send($pres());
        xmppServer.addHandler(on_message, null, "message", null);
        break;
      case Strophe.Status.DISCONNECTED:
        console.info("Disconnected with XMPP server.");
        if (self.onDisconnected ) self.onDisconnected();
        break;
      case Strophe.Status.DISCONNECTING:
        console.info("XMPP disconnecting.");
        break;
      case Strophe.Status.ATTACHED:
        console.info("XMPP attached.");
      }
    });

  };

  connect(serverAddress, token, chatId);

  /**
   * Send a video invitation to a remote user
   *
   * @memberOf Gab#
   * @param {string}
   *          uid Remote user's ID
   */
  this.sendVideoInvitation = function(uid) {
    xmppServer.send($msg({
      to : uid,
      'type' : 'chat'
    }).c('video-chat', {
      type : 'video-invitation'
    }));
  };
  /**
   * Send video agreed message to a remote user
   *
   * @memberOf Gab#
   * @param {string}
   *          uid Remote user's ID
   */
  this.sendVideoAccepted = function(uid) {
    xmppServer.send($msg({
      to : uid,
      type : 'chat'
    }).c('video-chat', {
      type : 'video-accepted'
    }));
  };

  /**
   * Send video denied message to a remote user
   *
   * @memberOf Gab#
   * @param {string}
   *          uid Remote user's ID
   */
  this.sendVideoDenied = function(uid) {
    xmppServer.send($msg({
      to : uid,
      type : 'chat'
    }).c('video-chat', {
      type : 'video-denied'
    }));
  };

  /**
   * Send video stopped message to a remote user
   *
   * @memberOf Gab#
   * @param {string}
   *          uid Remote user's ID
   */
  this.sendVideoStopped = function(uid) {
    xmppServer.send($msg({
      to : uid,
      type : 'chat'
    }).c('video-chat', {
      type : 'video-stopped'
    }));
  };

  /**
   * Send signal message to a remote user
   *
   * @memberOf Gab#
   * @param {string}
   *          uid Remote user's ID
   * @param {string}
   *          message Signal message
   */
  this.sendSignalMessage = function(uid, message) {
    console.log('C->S: ' + JSON.stringify(message));
    xmppServer.send($msg({
      to : uid,
      type : 'chat'
    }).c('video-chat', {
      type : 'video-signal',
      data : JSON.stringify(message)
    }));
  };

  /**
   * Send room join message to server
   *
   * @memberOf Gab#
   * @param {string}
   *          Room token.
   */
  this.sendJoinRoom = function(roomToken) {
    throw Woogeen.Error.P2P_CONN_SERVER_NOT_SUPPORTED;
  };

  /**
   * Send leave room message to server
   *
   * @memberOf Gab#
   */
  this.sendLeaveRoom = function() {
    throw Woogeen.Error.P2P_CONN_SERVER_NOT_SUPPORTED;
  };

  /**
   * Finalize
   *
   * @memberOf Gab#
   */
  this.finalize = function() {
    xmppServer.disconnect("Disconnect by user");
  };
}