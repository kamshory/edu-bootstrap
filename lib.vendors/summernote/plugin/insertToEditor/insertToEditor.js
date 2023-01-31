/**
 *
 * copyright [year] [your Business Name and/or Your Name].
 * email: your@email.com
 * license: Your chosen license, or link to a license file.
 *
 */
(function (factory) {
    /* Global define */
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node/CommonJS
        module.exports = factory(require('jquery'));
    } else {
        // Browser globals
        factory(window.jQuery);
    }
}(function ($) {
    /**
     * @class plugin.insertToEditor
     *
     * example Plugin
    */

    $.extend($.summernote.options, {
        insertToEditor: {
            icon: '<i class="note-icon-pencil"/>',
            tooltip: 'Insert To Editor'
        }
    });



    $.extend(true, $.summernote.lang, {
        'en-US': {
            /* US English(Default Language) */
            insertToEditor: {
                exampleText: 'Insert To Editor',
                dialogTitle: 'Insert To Editor',
                okButton: 'Close'
            }
        }
    });

    $.extend($.summernote.plugins, {
        /**
         *  @param {Object} context - context object has status of editor.
         */
        'insertToEditor': function (context) {
            var self = this,

                // ui has renders to build ui elements
                // for e.g. you can create a button with 'ui.button'
                ui = $.summernote.ui,
                $note = context.layoutInfo.note,

                // contentEditable element
                $editor = context.layoutInfo.editor,
                $editable = context.layoutInfo.editable,
                $toolbar = context.layoutInfo.toolbar,

                $editBtn = $(".btnEdit"),

                // options holds the Options Information from Summernote and what we extended above.
                options = context.options,

                // lang holds the Language Information from Summernote and what we extended above.
                lang = options.langInfo
                
                ;


            context.memo('button.insertToEditor', function () {

                // Here we create a button
                var button = ui.button({

                    // icon for button
                    contents: options.insertToEditor.icon,

                    // tooltip for button
                    tooltip: lang.insertToEditor.tooltip,

                    // Keep button from being disabled when in CodeView
                    codeviewKeepButton: true,

                    click: function (e) {
                        
                        let content = context.invoke('code');
                        let elem = document.createElement('div');
                        elem.innerHTML = content;
                        let endWithNL = false;
                        console.log(originalSelection+"'")
                        if(originalSelection.length > 0)
                        {
                            console.log('1')
                            if(originalSelection.substring(originalSelection.length - 1) == '\n')
                            {
                                console.log('2')
                                endWithNL = true;
                            }
                        }
                        let table = convertTableElementToMarkdown(elem.getElementsByTagName('table')[0]);
                        if(endWithNL)
                        {
                            console.log('3')
                            table += '\r\n';
                        }
                        window.parent.insertHTML(table);
                        window.parent.closeTableDialog();
                        
                    }
                });
                return button.render();
            });

            this.initialize = function () {

                // This is how we can add a Modal Dialog to allow users to interact with the Plugin.

                // get the correct container for the plugin how it's attached to the document DOM.
                // Using the current latest development branch, you can now use $.summernote.interface;
                // to return which Summernote is being used to be able to adjust the modal layout to suit.
                // using this.options.id will return a generated timestamp when Summernote was initiliased
                // on page to allow using unique ID's.
                var $container = options.dialogsInBody ? $(document.body) : $editor;

                // Build the Body HTML of the Dialog.
                var body = '<div class="form-group">' + '</div>';

                // Build the Footer HTML of the Dialog.
                var footer = '<button href="#" class="btn btn-primary note-insertToEditor-btn">' + lang.insertToEditor.okButton + '</button>';

                

                this.initialize = function () {

                    // This is how we can add a Modal Dialog to allow users to interact with the Plugin.

                    // get the correct container for the plugin how it's attached to the document DOM.
                    // Using the current latest development branch, you can now use $.summernote.interface;
                    // to return which Summernote is being used to be able to adjust the modal layout to suit.
                    // using this.options.id will return a generated timestamp when Summernote was initiliased
                    // on page to allow using unique ID's.
                    var $container = options.dialogsInBody ? $(document.body) : $editor;

                    // Build the Body HTML of the Dialog.
                    var body = '<div class="form-group">' + '</div>';

                };


                this.destroy = function () {
                    ui.hideDialog(this.$dialog);
                    this.$dialog.remove();
                };


                this.bindEnterKey = function ($input, $btn) {
                    $input.on('keypress', function (event) {
                        if (event.keyCode === 13) {
                            $btn.trigger('click');
                        }
                    });
                };


                this.bindLabels = function () {
                    self.$dialog.find('.form-control:first').focus().select();
                    self.$dialog.find('label').on('click', function () {
                        $(this).parent().find('.form-control:first').focus();
                    });
                };



               



            }
        }
    }
    );
}));



var NL = "\r\n";

function convertTableElementToMarkdown(tableEl) {
    var rows = [];
    var trEls = tableEl.getElementsByTagName('tr');
    for(var i=0; i<trEls.length; i++) {
        var tableRow = trEls[i];
        var markdownRow = convertTableRowElementToMarkdown(tableRow, i);
        rows.push(markdownRow);
        if(i == 0) {
            markdownRow = createMarkdownDividerRow(tableRow.children.length);
          rows.push(markdownRow);
        }
    }
    let fixedRows = rows;//fixCell(rows);
    let fixedRowsStr = [];
    for(let i in fixedRows)
    {
        fixedRowsStr[i] = '| '+fixedRows[i].join(' | ') + ' |';
    }
    return fixedRowsStr.join(NL);
}

function fixCell(rows, maxLength)
{
    let fixedRows = [];
    maxLength = maxLength || 2;
    let coolWidth = [];
    for(let i in rows)
    {
        let cels = rows[i];
        for(let j in cels)
        {
            if(typeof coolWidth[j] == 'undefined')
            {
                coolWidth[j] = cels[j].length;
            }
            else if(coolWidth[j] < cels[j].length && cels[j].length < maxLength)
            {
                coolWidth[j] = cels[j].length;
            }
        }
    }
    for(let i in rows)
    {
        let cels = rows[i];
        fixedRows[i] = [];
        for(let j in cels)
        {
            if(i == 1)
            {
                fixedRows[i][j] = padRight(rows[i][j], coolWidth[j], '-');
            }
            else
            {
                fixedRows[i][j] = padRight(rows[i][j], coolWidth[j], ' ');
            }
        }
    }
    return fixedRows;
}

function padRight(input, length, pad)
{
    let output = input;
    while(output.length < length)
    {
        output += pad;
    }
    return output;
}
function convertTableRowElementToMarkdown(tableRowEl, rowNumber) {
    var cells = [];
    var cellEls = tableRowEl.children;
    for(var i=0; i<cellEls.length; i++) {
        var cell = cellEls[i];
        let val = cell.innerText.toString();
        val = val.trim();
        cells.push(val);
    }
    return cells;
}

function createMarkdownDividerRow(cellCount) {
    var dividerCells = [];
    for(i = 0; i<cellCount; i++) {
        dividerCells.push('--');
    }
    return dividerCells;
}