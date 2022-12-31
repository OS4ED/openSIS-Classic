var loadingImage = new Image();
loadingImage.src = "assets/ajax-loader.gif";

function showLoading()
{
    var str;
    str = '<table><tr><td width=200px></td></tr>';
    str = str + '<tr><td align=center><img border=0 src=\'images/loading.gif\'></td></tr>';
    str = str + '<tr><td align=center>Loading...</td></tr></table>';

    return str;
}

function makeObject() {
    var x;
    if (window.ActiveXObject) {
        x = new ActiveXObject("Microsoft.XMLHTTP");
    } else if (window.XMLHttpRequest) {
        x = new XMLHttpRequest();
    }
    return x;
}
var request = makeObject();

var the_content;
function check_content(the_content) {
    $('#loading-image').show();
    $.ajax(the_content).done(function (data) {
        $('#content').html(data);
        $('#loading-image').hide();
    });
}
function parseCheck_content() {
    if (request.readyState == 1) {
        document.getElementById('content').innerHTML = '<center><img border=0 src=assets/ajax_loader.gif><br>Loading...</center>';
    }
    if (request.readyState == 4) {
        var answer = request.responseText;
        document.getElementById('content').innerHTML = answer;
    }
    calendar.hideCalendar();
}

function load_link(the_content) {

    the_content = the_content.replace("Modules.php", "Ajax.php");
    $('#loading-image').show();
    $.ajax(the_content).done(function (data) {
        $('#content').html(data);
        $('#loading-image').hide();
    });
//    request.open('get', the_content);
//    request.onreadystatechange = parseCheck_content;
//    request.send('');
}
function load_link_group(the_content, stat) {

    the_content = the_content.replace("Modules.php", "Ajax.php");
    request.open('get', the_content);

    request.onreadystatechange = function () {


        if (request.readyState == 1) {
            document.getElementById('content').innerHTML = '<center><img border=0 src=assets/ajax_loader.gif><br>Loading...</center>';
        }
        if (request.readyState == 4) {
            var answer = request.responseText;
            document.getElementById('content').innerHTML = answer;

        }

        if (stat == '1')
        {

document.getElementById('divErr').innerHTML ="<div class='alert alert-success alert-styled-left'><button type='button' class='close' data-dismiss='alert'><span>×</span><span class='sr-only'>Close</span></button>Member added successfully.</div>";

        }
        else if (stat == '2')
        {

            document.getElementById('divErr').innerHTML = "<div class='alert alert-danger alert-styled-left'><button type='button' class='close' data-dismiss='alert'><span>×</span><span class='sr-only'>Close</span></button>Member deleted successfully.</div>";

        }

    };
    request.send('');
}

function ajaxform(thisform, formhandler)
{
    var formdata = "";
    try {
        xmlhttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
    } catch (e) {
        alert("Error: Could not load page.");
    }
    for (i = 0; i < thisform.length; i++)
    {
        if (thisform.elements[i].value != 'Delete')
        {
            if (thisform.elements[i].type == "text") {
                formdata = formdata + thisform.elements[i].name + "=" + escape(thisform.elements[i].value) + "&";
            } else if (thisform.elements[i].type == "textarea") {
                formdata = formdata + thisform.elements[i].name + "=" + escape(thisform.elements[i].value) + "&";
            } else if (thisform.elements[i].type == "checkbox") {
                if (thisform.elements[i].value && thisform.elements[i].checked)
                    formdata = formdata + thisform.elements[i].name + "=" + thisform.elements[i].value + "&";
            } else if (thisform.elements[i].type == "radio") {
                if (thisform.elements[i].checked == true) {
                    formdata = formdata + thisform.elements[i].name + "=" + thisform.elements[i].value + "&";
                }
            } else {
                formdata = formdata + thisform.elements[i].name + "=" + escape(thisform.elements[i].value) + "&";
            }
        }
    }

    xmlhttp.onreadystatechange = function () {
        if ((xmlhttp.readyState == 4) && (xmlhttp.status == 200))
            return 'send';
        else
            return 'failed';
    }
    formhandler = formhandler.replace("Modules.php", "Ajax.php");
    if (formdata.length < 1900)
        check_content(formhandler + "&" + formdata + "ajax=true");
    else
    {
        xmlhttp.open("POST", formhandler, true);
        xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xmlhttp.setRequestHeader("Content-length", parameters.length);
        xmlhttp.setRequestHeader("Connection", "close");

        xmlhttp.send(formdata);
    }
}

function loadformani(thisform, formhandler)
{
    var formdata = "";
    try {
        xmlhttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
    } catch (e) {
        alert("Error: Could not load page.");
    }
    for (i = 0; i < thisform.length; i++)
    {
        if (thisform.elements[i].name != 'button' && thisform.elements[i].value != 'Delete')
        {
            if (thisform.elements[i].type == "text") {
                formdata = formdata + thisform.elements[i].name + "=" + escape(thisform.elements[i].value) + "&";
            } else if (thisform.elements[i].type == "textarea") {
                formdata = formdata + thisform.elements[i].name + "=" + escape(thisform.elements[i].value) + "&";
            } else if (thisform.elements[i].type == "checkbox") {
                formdata = formdata + thisform.elements[i].name + "=" + thisform.elements[i].checked + "&";
            } else if (thisform.elements[i].type == "radio") {
                if (thisform.elements[i].checked == true) {
                    formdata = formdata + thisform.elements[i].name + "=" + thisform.elements[i].value + "&";
                }
            } else {
                formdata = formdata + thisform.elements[i].name + "=" + escape(thisform.elements[i].value) + "&";
            }
        }
    }

    xmlhttp.onreadystatechange = function () {
        if ((xmlhttp.readyState == 4) && (xmlhttp.status == 200))
            return 'send';
        else
            return 'failed';


    }
    formhandler = formhandler.replace("Modules.php", "Ajax.php");
    xmlhttp.open("POST", formhandler, true);
    xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xmlhttp.setRequestHeader("Content-length", parameters.length);
    xmlhttp.setRequestHeader("Connection", "close");
    xmlhttp.send(formdata);
}

function grabA(alink)
{
    var oldlink = alink.href;
    oldlink = oldlink.replace("Modules.php", "Ajax.php");
    oldlink = oldlink + "&ajax=true";
    check_content(oldlink);

}

function cancelEvent(e) {
    if (!e)
        e = window.event;
    if (e.preventDefault) {
        e.preventDefault();
    } else {
        e.returnValue = false;
    }
}
function stopEvent(e) {
    if (!e)
        e = window.event;
    if (e.stopPropagation) {
        e.stopPropagation();
    } else {
        e.cancelBubble = true;
    }
}

function go_location_for_attendance(link_url)
{
    $.ajax({
        url: 'Ajax_url_encode.php',
        type: 'POST',
        data: {'link_url': link_url},
        success: function (encoded_url) {
            document.location.href = encoded_url;
        }
    });

}