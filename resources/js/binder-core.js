/**
 * The MIT License (MIT)
 * 
 * Copyright (c) 2014-2015 Ron Bodnar <rbodnar93@gmail.com>
 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 * */
$(document).ready(function () {
    /*
     * Navigation tabs
     */
    $(document).ready(function () {
        if (location.hash !== '') {
            $('a[href="' + location.hash + '"]').tab('show');
        }
        return $('a[data-toggle="tab"]').on('shown', function (e) {
            return location.hash = $(e.target).attr('href').substr(1);
        });
    });

    /*
     * Add binder modal
     */
    var modalButton = $('#modal-button');
    modalButton.click(function (e) {
        var inputValue = $('#binder-name-field').val();
        addTab('#' + inputValue);
        $('#addTabModal').modal('hide');
    });

    /**
     * Remove a Tab
     */
    $('#tab-container').on('click', ' li a .close', function () {
        if (confirm('Are you sure you want to permanently delete this binder?')) {
            $(this).parents('li').remove('li');
            $($(this).parents('li').children('a').attr('href')).remove();
            $('#tab-container a[href="#search"]').tab('show');
            var name = $(this).parents('li').children('a').attr('href');
            var strippedName = name.replace('#', '').replace(' ', '-');
            $.post('actions.php', {'name': strippedName, 'action': 'remove'}, function (response) {
                console.log(response);
            });
        }
    });

    /**
     * Click Tab to show its contents
     */
    $("#tab-container").on("click", "a", function (e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if (href !== '#add-tab') {
            $('#tab-container a[href="' + href + '"]').tab('show');
        }
    });

    /*
     * Automatically close certain alerts with a sliding effect after 3.5 seconds
     */
    window.setTimeout(function () {
        $(".alert").fadeTo(1500, 0).slideUp(500, function () {
            $(this).alert('close');
        });
    }, 3500);

    /*
     * Card image tooltips
     */
    $('tbody tr').qtip({
        show: {
            delay: '300',
            solo: true
        },
        content: {
            text: function (event, api) {
                return '<img src="' + $(this).attr('qtip-content') + '" width="200" height="300" />';
            }
        },
        position: {
            viewport: true,
            at: 'bottom center',
            my: 'top center'
        }
    });

    /*
     * Table sorting
     */
    $("#search-table").tablesorter({
        sortList: [[0, 0]]
    });
    $("[id^=binder-table]").tablesorter({
        sortList: [[0, 0]]
    });
});

function addTab(name) {
    var filteredName = name.replace(' ', '-').toLowerCase();
    var strippedName = name.replace('#', '').toLowerCase();
    var addTabString = '<li><a href="#add-tab" id="btnAddPage" data-toggle="modal" data-target="#addTabModal" title="Add a new binder"><span class="glyphicon glyphicon-plus" id="add-button"></span></a></li>\n';
    var bahh = '';
    var listElements = $('#tab-container > li');
    listElements.each(function () {
        var href = $(this).children('a').attr('href');
        if (href === '#add-tab') {
            return; // javascript equivalent to continue
        } else {
            bahh += '<li><a href="' + href + '" role="tab" data-toggle="tab">' + $(this).children('a').html() + '</a></li>\n';
        }
    });
    var listElement = $('#container ul');
    listElement.html(bahh + '<li><a href="' + filteredName + '" role="tab" data-toggle="tab">' + ucWords(strippedName) + '<button class="close" title="Remove" type="button">x</button></a></li>' + addTabString + '\n');

    var bahh2 = '';
    var tabPanes = $('div.tab-pane');
    tabPanes.each(function () {
        bahh2 += '<div class="tab-pane fade in" id="' + $(this).attr('id') + '" align="center">' + $(this).html() + '</div>\n';
    });

    var container = $('#tab-content');
    container.html(bahh2 + '<div class="tab-pane fade in" id="' + filteredName.replace('#', '') + '" align="center"></div>\n');

    $.post('actions.php', {name: '' + filteredName.replace('#', '') + '', action: 'add'}, function (response) {
        console.log(response);
    });

    console.log('added');

    $('#addTabModal').modal('hide');
    $('#binder-name-field').val('');
    $('#tab-container a[href="#' + name + ']').tab('show');
}

function ucFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function ucWords(str) {
    return str.replace(/\w\S*/g, function (txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}