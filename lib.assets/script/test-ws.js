function PlanetChat(websocketURL, options) {
	this.settings = {
		reconnectTimeout: 1000
	};

	options = options || {};

	for (let ii in options) {
		if (options.hasOwnProperty(ii) && _this.settings.hasOwnProperty(ii)) {
			settings[ii] = options[ii];
		}
	}

	this.conn = null;
	this.websocketURL = websocketURL;
	this.connected = false;
	this.firstConnect = true;
	this.toObject = null;
	this.init = function () {
		this.connect();
	}
	this.connect = function (websocketURL) {
		if (!websocketURL) {
			websocketURL = _this.websocketURL;
		}
		try {
			console.log('before connect ' + websocketURL)
			this.conn = new WebSocket(websocketURL);
			console.log('after connect ' + websocketURL)
			this.conn.opopen = function (evt) {
				console.log('Connected');
				_this.connected = true;
				_this.firstConnect = false;
				clearTimeout(_this.toObject);

				if (_this.firstConnect) {
					_this.onFirstConnect(evt);
				}
				else {
					_this.onReconnect(evt);
					_this.onReconnect = function (event) { };
				}
				_this.onOpen(e);
			}
			this.conn.operror = function (evt) {
				console.log('operror');
				_this.connected = false;
				_this.firstConnect = false;
				_this.onError(evt);

				clearTimeout(_this.toObj);
				_this.toObj = setTimeout(function () {
					_this.connect();
				}, _this.settings.reconnectTimeout);

			}
			this.conn.onclose = function (evt) {
				console.log('onclose');
				_this.connected = false;
				_this.firstConnect = false;
				_this.onClose(evt);
				clearTimeout(_this.toObj);
				_this.toObj = setTimeout(function () {
					_this.connect();
				}, _this.settings.reconnectTimeout);
			}
			this.conn.onmessage = function (evt) {
				console.log('onmessage');
				console.log('receive', evt.data);
				_this.connected = true;
				_this.onMessage(evt);
			}
		}
		catch (e) {
			console.error(e);
		}
	}
	this.onFirstConnect = function (evt) {
	};
	this.onReconnect = function (evt) {
	};
	this.onOpen = function (evt) {
	};
	this.onError = function (evt) {
	};
	this.onClose = function (evt) {
	};
	this.onMessage = function (message) {
	};
	this.send = function (message) {
		try {
			if (this.connected) {
				this.conn.send(message);
			}
			else {
				this.onReconnect = function (evt) {
					this.conn.send(message);
				};
				this.connect();
			}
		}
		catch (e) {
			this.onReconnect = function (evt) {
				this.conn.send(message);
			};
			this.connect();
		}

	};

	this.onBeforeSendMessage = function () {
	};
	this.onSendMessage = function () {
	}
	this.onAfterSendMessage = function () {
	};

	let _this = this;
	this.init();
}

