function planetChat(websocketURL, options)
{
    this.settings = {
        reconnectTimeout:1000
    };

    options = options || {};

    for(let ii in options)
    {
        if(options.hasOwnProperty(ii) && _this.settings.hasOwnProperty(ii))
        {
            settings[ii] = options[ii];
        }
    }

	this.conn = null;
	this.websocketURL = websocketURL;
	this.connected = false;
	this.firstConnect = true;	
	this.toObject = null;
	this.init = function()
	{
        this.connect();
	}
	this.connect = function(websocketURL)
	{
		if(!websocketURL)
		{
			websocketURL = _this.websocketURL;
		}
		try
		{
            console.log('before connect '+websocketURL)
			this.conn = new WebSocket(websocketURL);
            console.log('after connect '+websocketURL)
			this.conn.opopen = function(e){
				console.log('Connected');
				_this.connected = true;
				_this.firstConnect = false;
				clearTimeout(_this.toObject);

				if(_this.firstConnect)
				{
					_this.onFirstConnect(e);
				}
				else
				{
					_this.onReconnect(e);
					// Prevent executed twice
					_this.onReconnect = function(event){};
				}
				_this.onOpen(e);
			}
			this.conn.operror = function(e){
                console.log('operror');
				_this.connected = false;
				_this.firstConnect = false;
				_this.onError(e);

                clearTimeout(_this.toObj);
                _this.toObj = setTimeout(function(){
				    _this.connect();
                }, _this.settings.reconnectTimeout);

			}
			this.conn.onclose = function(e){
                console.log('onclose');
				_this.connected = false;
				_this.firstConnect = false;
				_this.onClose(e);
				clearTimeout(_this.toObj);
                _this.toObj = setTimeout(function(){
				    _this.connect();
                }, _this.settings.reconnectTimeout);
			}
			this.conn.onmessage = function(e){
                console.log('onmessage');
				console.log('receive', e.data);
				_this.connected = true;
				_this.onMessage(e);
			}
		}
		catch(e)
		{
			console.error(e);
		}
	}
	this.onFirstConnect = function(event)
	{
	};
	this.onReconnect = function(event)
	{
	};
	this.onOpen = function(e)
	{
	};
	this.onError = function(e)
	{
	};
	this.onClose = function(e)
	{
	};
	this.onMessage = function(m)
	{
	};
	this.send = function(message)
	{
		try
		{
			if(this.connected)
			{
				this.conn.send(message);
			}
			else
			{
				this.onReconnect = function(event)
				{
					this.conn.send(message);
				};
				this.connect();
			}
		}
		catch(e)
		{
			this.onReconnect = function(event)
			{
				this.conn.send(message);
			};
			this.connect();			
		}

	};
	
	this.onBeforeSendMessage = function()
	{
	};
	this.onSendMessage = function()
	{
	}
	this.onAfterSendMessage = function()
	{
	};
	
	let _this = this;
	this.init();
	
}

