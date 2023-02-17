let pChat = new planetChat(websocketURL);

pChat.onOpen = function(e){
};
pChat.onError = function(e) {
};
pChat.onClose = function(e) {

};
pChat.onMessage = function(e){
    let message = e.data;
    try
    {
        let msg = JSON.parse(message);
        if(msg.command == 'test-member')
        {
            buildTestMember(msg.data[0].test_member, testId, '.test-member');
        }
    }
    catch(e)
    {

    }
};

function buildTestMember(data, testId, selector)
{
    $(selector).empty();
    if(typeof data[testId] != 'undefined')
    {
        for(let i in data[testId])
        {
            appendTestMember(data[testId][i], selector)
        }
    }
}

function appendTestMember(data, selector)
{
    let div1 = $('<div>');
    div1
        .addClass('col-xl-3')
        .addClass('col-lg-4')
        .addClass('col-md-6')
        .addClass('col-sm-12')
        .attr({'data-id': data.username});
    let div2 = $('<div>');
    div2.addClass('card');
    let div3 = $('<div>');
    div3.addClass('card-body');
    let h5 = $('<h5>');
    h5.addClass('card-title').text(data.name);
    
    div3.append(h5);

    let btn1 = $('<button>');
    btn1.addClass('btn')
    .addClass('btn-sm')
    .addClass('warning')
    let span1 = $('<i>');
    span1
        .addClass('fas')
        .addClass('fa-triangle-exclamation');
    btn1.append(span1);

    div3.append(btn1);

    let btn2 = $('<button>');
    btn2.addClass('btn')
    .addClass('btn-sm')
    .addClass('kick')
    let span2 = $('<i>');
    span2
        .addClass('fas')
        .addClass('fa-remove');
    btn2.append(span2);

    div3.append(btn2);


    div2.append(div3);
    div1.append(div2);
    div1.appendTo(selector);
}

pChat.sendMessageToStudent = function(title, message, recipient){
    let data = JSON.stringify({
        command:'message',
        receiver:[recipient],
        data:{
            title:title,      
            message:message,
            icon:'fa-warning'                    
        }           
    });
    pChat.send(data);
}

pChat.kickStudent = function(title, message, recipient){
    let data = JSON.stringify({
        command:'kick',
        receiver:[recipient],
        data:
        {
            title:title,      
            message:message,
            icon:'fa-remove'                    
        }
        
    });
    pChat.send(data);
}


$(document).ready(function(e){
    $(document).on('click', '.warning', function(e){
        pChat.sendMessageToStudent('Peringatan Pengawas', 'Harap jangan berisik!', $(this).closest('.card').parent().attr('data-id'))
    });
    $(document).on('click', '.kick', function(e){
        pChat.kickStudent('Tindakan Pengawas', 'Anda dikeluarkan dari ujian!', $(this).closest('.card').parent().attr('data-id'))
    });
});