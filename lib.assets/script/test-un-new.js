let testData = [];
if (typeof testDataJSON != 'undefined') {
    testData = testDataJSON.data;
}
let numberingList = {
    'upper-alpha': ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'],
    'lower-alpha': ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'],
    'upper-roman': ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'],
    'lower-roman': ['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'vii', 'ix', 'x'],
    'decimal': ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
    'decimal-leading-zero': ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10']
};

let keyIndex = 'picoedu_last_index_' + testStudentId;
let keyAnswer = 'picoedu_answer_' + testStudentId;
let lastIndexStr = window.localStorage.getItem(keyIndex) || '0';
let lastIndex = parseInt(lastIndexStr);
if (isNaN(lastIndex)) {
    lastIndex = 0;
}
let selector1 = '.test-wrapper';
let selector2 = '.selector-wrapper';
let answerStr = window.localStorage.getItem(keyAnswer) || '{}';
let answer = {};
try {
    answer = JSON.parse(answerStr);
}
catch (e) {
    answer = {};
}

$(document).ready(function () {

    $(document).on('click', selector2 + ' li a', function (e) {
        e.preventDefault();
        let a = $(this);
        a.parent().siblings().removeClass('active');
        a.parent().addClass('active');
        let indexStr = a.parent().attr('data-index');
        let index = parseInt(indexStr);
        if(isNaN(index))
        {
            index = 0;
        }

        lastIndex = index;

        window.localStorage.setItem(keyIndex, index);      

        renderQuestion(testData, index, selector1, answer);
        setAnswer(testData, index, selector1, selector2, answer);
        setActiveNumber(testData, index, selector1, selector2, answer);
        markDoubtful(testData, index, selector1, selector2, answer);
    });

    $(document).on('change', selector1 + ' .test-option-area .option-control input', function (e) {
        e.preventDefault();
        let input = $(this);
        input.parent().siblings().removeClass('active');
        input.parent().addClass('active')
        let index = input.attr('data-index');
        let questionId = input.attr('data-question-id');
        let optionId = input.val();

        answer[questionId] = (typeof answer[questionId] == 'undefined') ? {} : answer[questionId];
        answer[questionId].answerId = optionId;
        window.localStorage.setItem(keyAnswer, JSON.stringify(answer));
        setActiveNumber(testData, index, selector1, selector2, answer);
        saveAnswer(JSON.parse(JSON.stringify(answer)));
    });

    $(document).on('click', '.button-doubtful', function (e) {
        doubtfulAnswer();
    });

    $(document).on('click', '.button-prev', function (e) {
        prev();
    });

    $(document).on('click', '.button-next', function (e) {
        next();
    });

    $("#sidebarCollapse").on("click", function () {
        $("#sidebar").toggleClass("active");
    });

    $(document).on("keydown", 'body', function (event) {

        let key = event.key;
        key = key.toUpperCase();
        if (key == "ArrowRight".toUpperCase()) {
            next();
        }
        if (key == "ArrowLeft".toUpperCase()) {
            prev();
        }
        if (key == 'R') {
            doubtfulAnswer();
        }
        $('.test-option-area .option-control').each(function (ev) {
            if ($(this).find('label').text().trim() == key) {
                $(this).find('input')[0].checked = true;
                let optionId = $(this).find('input').val();
                updateQuestionSelector(optionId);
                saveAnswer(JSON.parse(JSON.stringify(answer)));
            }
        });
        
    });

    renderQuestion(testData, lastIndex, selector1, answer);
    renderQuestionSelector(testData, lastIndex, selector2, answer);

    setAnswer(testData, lastIndex, selector1, selector2, answer);
    setActiveNumber(testData, lastIndex, selector1, selector2, answer);
    markDoubtful(testData, lastIndex, selector1, selector2, answer);
});

function saveAnswer(answerToSaved)
{
    let answerId = testDataJSON.answer.answer_id;
    let testId = testDataJSON.test.test_id;
    $.ajax({
        'type':'POST',
        'url':'siswa/simpan-jawaban.php',
        'dataType':'json',
        'data':{test_id:testId, answer_id:answerId, answer:answerToSaved},
        'success':function(data){
            console.log(data);
        },
        'error':function(data){
            console.log(data);
        }
    });
}

function updateQuestionSelector(optionId) {
    let question = testData[lastIndex];
    let questionId = question.question_id;

    answer[questionId] = (typeof answer[questionId] == 'undefined') ? {} : answer[questionId];
    answer[questionId].answerId = optionId;
    window.localStorage.setItem(keyAnswer, JSON.stringify(answer));
    setActiveNumber(testData, lastIndex, selector1, selector2, answer);
}

function doubtfulAnswer() {
    let question = testData[lastIndex];
    let questionId = question.question_id;
    answer[questionId] = (typeof answer[questionId] == 'undefined') ? {} : answer[questionId];
    let doubtful = answer[questionId].doubtful || false;
    answer[questionId].doubtful = !doubtful;
    window.localStorage.setItem(keyAnswer, JSON.stringify(answer));
    setActiveNumber(testData, lastIndex, selector1, selector2, answer);
    markDoubtful(testData, lastIndex, selector1, selector2, answer);
}

function prev() {
    if (lastIndex > 0) {
        lastIndex--;
        window.localStorage.setItem(keyIndex, lastIndex)
        changeIndex(testData, lastIndex, selector1, selector2, answer)
    }
}

function next() {
    if (lastIndex < (testData.length - 1)) {
        lastIndex++;
        window.localStorage.setItem(keyIndex, lastIndex)
        changeIndex(testData, lastIndex, selector1, selector2, answer)
    }
}

function changeIndex(testData, index, selector1, selector2, answer) {
    renderQuestion(testData, index, selector1, answer);
    setAnswer(testData, index, selector1, selector2, answer);
    setActiveNumber(testData, index, selector1, selector2, answer);
    markDoubtful(testData, index, selector1, selector2, answer);
}

function setActiveNumber(testData, index, selector1, selector2, answer) {
    let listSelector = selector2 + ' li[data-index="' + index + '"]';
    let li = $(listSelector);
    li.siblings().removeClass('active');
    li.addClass('active');
    let question = testData[index];
    let questionId = question.question_id;

    if (typeof answer[questionId] != 'undefined') {
        let answered = (answer[questionId].answerId || '') != '';
        li.attr({ 'data-answered': answered ? 'true' : 'false' });
    }
}

function setAnswer(testData, index, selector1, selector2, answer) {  
    let question = testData[index];
    let questionId = question.question_id;
    if (typeof answer[questionId] != 'undefined') {
        let optionId = answer[questionId].answerId || '';
        let optionArea = $(selector1);
        let inputSelector = '#' + 'option_' + optionId;
        if (optionId != '' && optionArea.find(inputSelector).length) {
            optionArea.find(inputSelector)[0].checked = true;
        }
    }
}

function markDoubtful(testData, index, selector1, selector2, answer) {
    let question = testData[index];
    let questionId = question.question_id;
    if (typeof answer[questionId] != 'undefined') {
        let doubtful = (answer[questionId].doubtful || false);
        let listSelector = selector2 + ' li[data-index="' + index + '"]';
        let li = $(listSelector);
        li.attr({ 'data-doubtful': doubtful ? 'true' : 'false' });
        $('body').attr('data-doubtful', doubtful ? 'true' : 'false');

        let answered = (answer[questionId].answerId || '') != '';
        li.attr({ 'data-doubtful': doubtful ? 'true' : 'false' });
        li.attr({ 'data-answered': answered ? 'true' : 'false' });
    }
}

function renderQuestion(testData, index, selector, answer) {
    let question = testData[index];
    let questionId = question.question_id;
    let sel = $(selector);
    let optionArea = sel.find('.test-option-area');
    let questionArea = sel.find('.test-question-area');
    optionArea.empty();
    questionArea.empty();
    questionArea.append(question.content);
    for (let i in question.option) {
        let option = question.option[i];
        let testOption = $('<div />');
        let inputControl = $('<div />');
        testOption.addClass('test-option');
        inputControl.addClass('option-control');
        let input = $('<input />');
        let label = $('<label />');
        let span1 = $('<span />');
        input.attr({ 'type': 'radio', 'data-index': index, 'data-question-id': questionId, 'name': 'option_' + questionId, 'id': 'option_' + option.option_id, 'value': option.option_id });
        label.attr({ 'for': 'option_' + option.option_id });
        span1.text(getNumber(numberingList, question.numbering, i));
        label.append(span1);
        inputControl.append(input).append(label);
        testOption.append(inputControl).append(option.content);
        optionArea.append(testOption);
    }
}

function getNumber(numberingList, numbering, number) {
    let type = numberingList[numbering];
    return type[parseInt(number)];
}

function renderQuestionSelector(testData, index, selector, answer) {
    let ul = $('<ul />');
    for (let i in testData) {
        let question = testData[i];
        let questionId = question.question_id;
        let j = parseInt(i) + 1;
        let li = $('<li />');
        li.attr({ 'data-index': i });

        if (i == index) {
            li.addClass('active');
        }

        if (typeof answer[questionId] != 'undefined') {
            let doubtful = (answer[questionId].doubtful || false);
            let answered = (answer[questionId].answerId || '') != '';
            li.attr({ 'data-doubtful': doubtful ? 'true' : 'false' });
            li.attr({ 'data-answered': answered ? 'true' : 'false' });

        }
        let a = $('<a />');
        a.attr({ 'href': '#' });
        a.text(j);
        li.append(a);
        ul.append(li);
    }
    $(selector).empty().append(ul);
}
