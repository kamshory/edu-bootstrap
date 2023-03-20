let pChat = new PlanetChat(websocketURL);

pChat.onOpen = function(e) {
};

pChat.onError = function(e) {
};

pChat.onClose = function(e) {
};

pChat.onMessage = function(e) {
    let message = e.data;
    try
    {
        let msg = JSON.parse(message);
        if(msg.command == 'message')
        {
            showMessage(msg.data.title, msg.data.message, msg.data.icon);
        }
        if(msg.command == 'kick')
        {
            kickStudent(msg.data.title, msg.data.message, msg.data.icon);
        }
    }
    catch(e)
    {

    }
};

function showMessage(title, message, icon) {
    $('#test-alert .modal-body').empty().append($('<p>').text(message));
    $('#test-alert .modal-title').text(' '+title);
    if(icon)
    {
        $('#test-alert .modal-title').prepend($('<i>').addClass('fas').addClass('fas').addClass(icon));
    }
    $('#test-alert').modal('show');
    setTimeout(function(){
        $('#test-alert').modal('hide');
    }, 5000);
}

function kickStudent(title, message, icon) {
    $('#test-alert .modal-body').empty().append($('<p>').text(message));
    $('#test-alert .modal-title').text(' '+title);
    if(icon)
    {
        $('#test-alert .modal-title').prepend($('<i>').addClass('fas').addClass(icon));
    }
    $('#test-alert').modal('show');
    setTimeout(function(){
        $('#test-alert').modal('hide');
    }, 5000);
}

$(document).ready(function(e){
    $(document).on('click', '.button-help', function(e1){
        e1.preventDefault();
        let type = $(this).attr('data-type');
        let json = {
            command:'help',
            receiver_group:['teacher', 'admin'],
            data:{
                type:type
            }
        };
        pChat.send(JSON.stringify(json));
    })
   
});