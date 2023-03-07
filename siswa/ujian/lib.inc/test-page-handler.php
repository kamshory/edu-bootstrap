<?php

$list = $picoTest->getQuestionList($studentLoggedIn, $eduTest, $token);
$question = $picoTest->getQuestion($list);
$testDataJSON = json_encode($question);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ujian</title>
    <base href="<?php echo $cfg->base_assets;?>siswa">
    <link rel="stylesheet" href="<?php echo $cfg->base_url;?>lib.vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_assets;?>lib.assets/fonts/roboto/font.css">

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_url;?>lib.vendors/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cfg->base_url;?>lib.assets/theme/default/css/test-un-new.css">

    <!-- Favicons -->
    <link rel="apple-touch-icon" href="<?php echo $cfg->base_assets;?>lib.favs/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" href="<?php echo $cfg->base_assets;?>lib.favs/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="<?php echo $cfg->base_assets;?>lib.favs/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="manifest" href="<?php echo $cfg->base_assets;?>lib.favs/manifest.json">
    <link rel="mask-icon" href="<?php echo $cfg->base_assets;?>lib.favs/safari-pinned-tab.svg" color="#563d7c">
    <link rel="icon" href="<?php echo $cfg->base_assets;?>lib.favs/favicon.ico">
    <meta name="msapplication-config" content="<?php echo $cfg->base_assets;?>lib.favs/browserconfig.xml">
    <script type="text/javascript" src="<?php echo $cfg->base_url;?>lib.vendors/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="<?php echo $cfg->base_url;?>lib.vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        let numberingList = {
            'upper-alpha' : ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'],
            'lower-alpha' : ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j'],
            'upper-roman' : ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'],
            'lower-roman' : ['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'vii', 'ix', 'x'],
            'decimal' : ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10'],
            'decimal-leading-zero' : ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10']
        };
        let testDataJSON = <?php echo $testDataJSON;?>;
        let testStudentId = '<?php echo $studentLoggedIn->student_id.$eduTest->test_id;?>';
        let keyIndex = 'picoedu_last_index_'+testStudentId;
        let keyAnswer = 'picoedu_answer_'+testStudentId;
        let lastIndexStr = window.localStorage.getItem(keyIndex) || '0';
        let lastIndex = parseInt(lastIndexStr);
        if(isNaN(lastIndex))
        {
            lastIndex = 0;
        }
        let selector1 = '.test-wrapper';
        let selector2 = '.selector-wrapper';
        let answerStr = window.localStorage.getItem(keyAnswer) || '{}';
        let answer = {};
        try
        {
            answer = JSON.parse(answerStr);
        }
        catch(e)
        {
            answer = {};
        }

        $(document).ready(function(){
            $(document).on('click', selector2 + ' li a', function(e){
                e.preventDefault();
                let a = $(this);
                a.parent().siblings().removeClass('active');
                a.parent().addClass('active')
                let index = a.attr('data-index');
                window.localStorage.setItem(keyIndex, index)
                renderQuestion(testDataJSON, index, selector1, answer);
            });
            $(document).on('change', selector1 + ' .test-option-area .option-control input', function(e){
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
            });
            $(document).on('click', '.button-doubtful', function(e){
                let question = testDataJSON[lastIndex];
                let questionId = question.question_id;
                answer[questionId] = (typeof answer[questionId] == 'undefined') ? {} : answer[questionId];
                let doubtful = answer[questionId].doubtful || false;
                answer[questionId].doubtful = !doubtful;
                window.localStorage.setItem(keyAnswer, JSON.stringify(answer));   
                markDoubtful(testDataJSON, lastIndex, selector1, answer);
            });

            renderQuestion(testDataJSON, lastIndex, selector1, answer);
            
            renderQuestionSelector(testDataJSON, lastIndex, selector2, answer);
        });
        function markDoubtful(testDataJSON, index, selector, answer)
        {
            let question = testDataJSON[index];
            let questionId = question.question_id;
            console.log(questionId)
            console.log(answer)
            let doubtful = answer[questionId].doubtful;
            console.log(doubtful);
            $('body').attr('data-doubtful', doubtful?'true':'false');
        }
        function renderQuestion(testDataJSON, index, selector, answer)
        {
            let question = testDataJSON[index];
            let sel = $(selector);
            let optionArea = sel.find('.test-option-area');
            let questionArea = sel.find('.test-question-area');
            optionArea.empty();
            questionArea.empty();
            questionArea.append(question.content);
            for(let i in question.option){
                let option = question.option[i];
                let testOption = $('<div />');
                let inputControl = $('<div />');
                testOption.addClass('test-option');
                inputControl.addClass('option-control');
                let input = $('<input />');
                let label = $('<label />');
                let span1 = $('<span />');
                input.attr({'type':'radio', 'data-index':index, 'data-question-id':question.question_id, 'name':'option_'+question.question_id, 'id':'option_'+option.option_id, 'value':option.option_id});
                label.attr({'for':'option_'+option.option_id});
                span1.text(getNumber(numberingList, question.numbering, i));
                label.append(span1);
                inputControl.append(input).append(label);
                testOption.append(inputControl).append(option.content);
                optionArea.append(testOption);
            }
            let optionId = answer[question.question_id].answerId || '';
            if(optionId != '' && optionArea.find('#'+'option_'+optionId).length)
            {
                optionArea.find('#'+'option_'+optionId)[0].checked = true;
            }
            markDoubtful(testDataJSON, index, selector, answer);
        }
        function getNumber(numberingList, numbering, number)
        {
            let type = numberingList[numbering];
            return type[parseInt(number)];
        }
        function renderQuestionSelector(testDataJSON, index, selector, answer)
        {
            let ul = $('<ul />');
            for(let i in testDataJSON)
            {
                let j = parseInt(i) + 1;
                let li = $('<li />');
                let a = $('<a />');
                a.attr({'href':'#', 'data-index':i});
                a.text(j);
                li.append(a);
                ul.append(li);
            }
            $(selector).empty().append(ul);
        }
    </script>

</head>

<body className="snippet-body">
    <div class="wrapper">
        <nav aria-label="Sidebar" id="sidebar">
            <div class="sidebar-header">
                <h3>UJIAN</h3>
                <hr>
            </div>
            <ul class="list-unstyled CTAs">
                <li> <a href="#" class="download">Subscribe</a> </li>
            </ul>
        </nav>
        <div class="content">
            <nav aria-label="Main Menu" class="navbar navbar-expand-lg navbar-light bg-light"> 
                <button type="button" id="sidebarCollapse"
                    class="btn btn-secondary"> <i class="fa fa-align-justify"></i> </button> <button class="navbar-toggler"
                    type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav"
                    aria-expanded="false" aria-label="Toggle navigation"> <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item active"> <a class="nav-link" href="#">Depan</a></li>
                        <li class="nav-item"> <a class="nav-link" href="#">Informasi</a></li>
                        <li class="nav-item"> <a class="nav-link" href="#">Keluar</a></li>
                    </ul>
                </div>
            </nav>
            <div class="content-wrapper">
                <div class="row">
                    <div class="col col-9 test-area">
                        <div class="test-wrapper">
                            <div class="test-question-area">
                            </div>
                            <div class="test-option-area">
                             
                            </div>                           
                        </div>
                        <div class="test-nav">
                            <a class="btn btn-primary" href="#">Sebelumnya</a>
                            <a class="btn btn-warning button-doubtful" href="#">Ragu-Ragu</a>
                            <a class="btn btn-primary" href="#">Sesudahnya</a>                               
                        </div>
                    </div>
                    <div class="col col-3 selector-area">
                        
                        <div class="selector-wrapper">
                            <ul>
                                <li><a href="#">1</a></li>
                                <li><a href="#">2</a></li>
                                <li><a href="#">3</a></li>
                                <li><a href="#">4</a></li>
                                <li><a href="#">5</a></li>
                                <li><a href="#">6</a></li>
                                <li><a href="#">7</a></li>
                                <li><a href="#">8</a></li>
                                <li><a href="#">9</a></li>
                                <li><a href="#">10</a></li>
                                <li><a href="#">11</a></li>
                                <li><a href="#">12</a></li>
                                <li><a href="#">13</a></li>
                                <li><a href="#">14</a></li>
                                <li><a href="#">15</a></li>
                                <li><a href="#">16</a></li>
                                <li><a href="#">17</a></li>
                                <li><a href="#">18</a></li>
                                <li><a href="#">19</a></li>
                                <li><a href="#">20</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="separator"></div>
                
            </div>
        </div>
    </div>
    <script type="text/javascript">$(document).ready(function () {
            $("#sidebarCollapse").on("click", function () {
                $("#sidebar").toggleClass("active");
            });


        $(document).on("keydown", 'body', function(event) {
            let key = event.key;
            key = key.toUpperCase();
            $('.test-option-area .option-control').each(function(ev){
                if($(this).find('label').text().trim() == key)
                {
                    $(this).find('input')[0].checked = true;
                }
            });

            
        });
        });</script>
    <script type="text/javascript">
        var myLink = document.querySelectorAll('a[href="#"]');
        myLink.forEach(function (link) {
            link.addEventListener("click", function (e) {
                e.preventDefault();
            });
        });
    </script>
</body>

</html>