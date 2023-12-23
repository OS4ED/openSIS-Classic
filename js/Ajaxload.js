function loadajax(frmname)
{
    this.formobj = document.forms[frmname];
    if (!this.formobj)
    {
        alert("BUG: couldnot get Form object " + frmname);
        return;
    }
    if (this.formobj.onsubmit)
    {
        this.formobj.old_onsubmit = this.formobj.onsubmit;
        this.formobj.onsubmit = null;
    } else
    {
        this.formobj.old_onsubmit = null;
    }
    this.formobj.onsubmit = ajax_handler;

}

function ajax_handler()
{
    if (ajaxform(this, this.action) == 'failed')
        return true;

    return false;
}

function formload_ajax(frm) {

    var frmloadajax = new loadajax(frm);
}



var hand = function (str) {
    window.document.getElementById('response_span').innerHTML = str;
}



function ajax_call(url, callback_function, error_function) {

    // New jQuery Ajax implemented on Aug 03, 2017
    $.ajax(url).done(function (data) {
        callback_function(data);
    }).fail(function (error_data) {
        error_function(error_data);
    });

    /*var xmlHttp = null;
     try {
     // for standard browsers
     xmlHttp = new XMLHttpRequest();
     } catch (e) {
     // for internet explorer
     try {
     xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
     } catch (e) {
     xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
     }
     }
     xmlHttp.onreadystatechange = function () {
     if (xmlHttp.readyState == 4)
     try {
     if (xmlHttp.status == 200) {
     
     callback_function(xmlHttp.responseText);
     }
     } catch (e) {
     
     error_function(e.description);
     }
     }
     
     xmlHttp.open("GET", url);
     xmlHttp.send(null);*/
}
// --------------------------------------------------- USER ----------------------------------------------------------------------------------- //

function usercheck_init(i, userid , profileid) {
    var obj = document.getElementById('ajax_output');
    obj.innerHTML = '';

    if (i.value.length < 1)
        return;

    var err = new Array();
    if (i.value.match(/[^A-Za-z0-9_@.]/))
        err[err.length] = 'Username can only contain letters, numbers, underscores, at the rate and dots';
    if (i.value.length < 3)
        err[err.length] = 'Username too short';
    if (err != '') {
        obj.style.color = '#ff0000';
        obj.innerHTML = err.join('<br />');

        if(i.value.length > 1)
        {
            window.$("#staff_username_flag").val("0");
            window.$("#mod_staff_btn").attr("disabled", true);
        }

        return;
    }

    window.$("#staff_username_flag").val("1");
    window.$("#mod_staff_btn").attr("disabled", false);

    var pqr = i.value;

    ajax_call('Validator.php?u=' + i.value + 'user' + '&userid=' + userid + '&profileid=' + profileid, usercheck_callback, usercheck_error);
}

function usercheck_callback(data) {
    var obj = document.getElementById('ajax_output');
    if (data == 1)
        obj.style.color = '#008800';
    else
        obj.style.color = '#ff0000';
    if (data == 1)
        obj.innerHTML = 'Username Available';
    else
        obj.innerHTML = 'Username already taken';


    var staff_username = document.getElementById("USERNAME").value;
    var staff_username_flag = document.getElementById("staff_username_flag").value;

    if(staff_username != '' && staff_username_flag == '0')
    {
        var obj = document.getElementById('ajax_output');
        obj.style.color = '#ff0000';
        obj.innerHTML = 'Username can only contain letters, numbers, underscores, at the rate and dots';

        window.$("#mod_staff_btn").attr("disabled", true);
    }
    else
    {
        window.$("#mod_staff_btn").attr("disabled", false);
    }
}

function usercheck_init_mod(i, opt) {
    var obj = document.getElementById('ajax_output_' + opt);
    obj.innerHTML = '';

    if (i.value.length < 1)
        return;

    var err = new Array();
    if (i.value.match(/[^A-Za-z0-9_@.]/))
        err[err.length] = 'Username can only contain letters, numbers, underscores, at the rate and dots';
    if (i.value.length < 3)
        err[err.length] = 'Username Too Short';
    if (err != '') {
        obj.style.color = '#ff0000';
        obj.innerHTML = err.join('<br />');

        if(i.value.length > 1)
        {
            window.$("#staff_username_flag").val("0");
            window.$("#mod_staff_btn").attr("disabled", true);
        }

        return;
    }

    window.$("#staff_username_flag").val("1");
    window.$("#mod_staff_btn").attr("disabled", false);

    var pqr = i.value;

    if (opt == '1')
        ajax_call('Validator.php?u=' + i.value + 'user', usercheck_callback_p, usercheck_error);

    if (opt == '2')
        ajax_call('Validator.php?u=' + i.value + 'user', usercheck_callback_s, usercheck_error);
}

function usercheck_callback_p(data) {
    var response = (data == 1);

    var obj = document.getElementById('ajax_output_1');
    obj.style.color = (response) ? '#008800' : '#ff0000';
    obj.innerHTML = (response == 1) ? 'Username Available' : 'Username already taken';

    var staff_username = document.getElementById("USERNAME").value;
    var staff_username_flag = document.getElementById("staff_username_flag").value;

    if(staff_username != '' && staff_username_flag == '0')
    {
        var obj = document.getElementById('ajax_output_1');
        obj.style.color = '#ff0000';
        obj.innerHTML = 'Username can only contain letters, numbers, underscores, at the rate and dots';

        window.$("#mod_staff_btn").attr("disabled", true);
    }
    else
    {
        window.$("#mod_staff_btn").attr("disabled", false);
    }
}

function usercheck_callback_s(data) {
    var response = (data == 1);

    var obj = document.getElementById('ajax_output_2');
    obj.style.color = (response) ? '#008800' : '#ff0000';
    obj.innerHTML = (response == 1) ? 'Username Available' : 'Username already taken';

    var staff_username = document.getElementById("USERNAME").value;
    var staff_username_flag = document.getElementById("staff_username_flag").value;

    if(staff_username != '' && staff_username_flag == '0')
    {
        var obj = document.getElementById('ajax_output_1');
        obj.style.color = '#ff0000';
        obj.innerHTML = 'Username can only contain letters, numbers, underscores, at the rate and dots';

        window.$("#mod_staff_btn").attr("disabled", true);
    }
    else
    {
        window.$("#mod_staff_btn").attr("disabled", false);
    }
}


function usercheck_error(err) {
    alert("Error: " + err);
}
function grab_GradeLevel(school_id)
{

    ajax_call('GrabGradeLevel.php?id=' + school_id, grab_GradeLevel_callback, grab_GradeLevel_error);
}

function Course_Mrinfo(schedule_id)
{
    ajax_call('CourseMoreInfo.php?id=' + schedule_id, Course_Mrinfo_callback, Course_Mrinfo_error);
}

function Course_Mrinfo_error()
{
    alert('Invalid Course');
}

function Course_Mrinfo_callback(data)
{
    document.getElementById('more_info_lbl').innerHTML = data;
    document.getElementById("more_info_lbl").getElementsByClassName("panel-heading")[0].style.display = 'none';
}

function cleanTempData()
{
    ajax_call('CleanTempSchedule.php');
        
}

function grab_coursePeriod(id, table, column_name)
{

    ajax_call('ChooseCP.php?id=' + id + '&table_name=' + table + '&column_name=' + column_name, grab_coursePeriod_callback, grab_GradeLevel_error);
}

function grab_coursePeriod_callback(data)
{
    var tdata = data.split('||');

//if(tdata[0].trim()==1)
//{

    var obj = document.getElementById(tdata[0].trim());
    obj.innerHTML = tdata[1];
//    }

//    if(tdata[0].trim()=='2')
//{
//   
//    var obj = document.getElementById('course_modal');
//    obj.innerHTML = tdata[1];
//    }
}
function grab_GradeLevel_callback(data)
{

    var obj = document.getElementById('grab_grade');
    obj.innerHTML = data;
}
function grab_GradeLevel_error()
{
    alert('Not working');
}
// ------------------------------------------------------ USER ---------------------------------------------------------------------------------- //
function usercheck_init_staff(i, userid, profileid) {

    var obj = document.getElementById('ajax_output_st');
    obj.innerHTML = '';
    document.getElementById('usr_err_check').value = '0';
    if (i.value.length < 1)
        return;

    var err = new Array();
    if (i.value.match(/[^A-Za-z0-9_@.]/))
        err[err.length] = 'Username can only contain letters, numbers, underscores, at the rate and dots';
    if (i.value.length < 3)
        err[err.length] = 'Username Too Short';
    if (err != '') {
        obj.style.color = '#ff0000';
        obj.innerHTML = err.join('<br />');

        if(i.value.length > 1)
        {
            window.$("#staff_username_flag").val("0");
            window.$("#mod_staff_btn").attr("disabled", true);
        }

        return;
    }

    window.$("#staff_username_flag").val("1");
    window.$("#mod_staff_btn").attr("disabled", false);

    ajax_call('Validator.php?u=' + i.value + 'user'+'&userid='+ userid+'&profileid='+ profileid, usercheck_callback_staff);
}

function usercheck_init_staff_2(i) {
    var obj = document.getElementById('ajax_output_st');
    obj.innerHTML = '';
    document.getElementById('usr_err_check').value = '0';
    if (i.value.length < 1)
        return;

    var err = new Array();
    if (i.value.match(/[^A-Za-z0-9_@.]/))
        err[err.length] = 'Username can only contain letters, numbers, underscores, at the rate and dots';
    if (i.value.length < 3)
        err[err.length] = 'Username Too Short';
    if (err != '') {
        obj.style.color = '#ff0000';
        obj.innerHTML = err.join('<br />');

        if(i.value.length > 1)
        {
            window.$("#staff_username_flag").val("0");
            window.$("#mod_staff_btn").attr("disabled", true);
        }

        return;
    }

    window.$("#staff_username_flag").val("1");
    window.$("#mod_staff_btn").attr("disabled", false);

    ajax_call('Validator.php?u=' + i.value + 'stud', usercheck_callback_staff);
}

function usercheck_callback_staff(data) {

    var response = data;
    if (response == 1)
        document.getElementById('usr_err_check').value = '1';
    var obj = document.getElementById('ajax_output_st');
    obj.style.color = (response == 1) ? '#008800' : '#ff0000';
    obj.innerHTML = (response == 1) ? 'Username Available' : 'Username already taken';
    if (response != 1)
        document.getElementById("USERNAME").value = '';
}
// ------------------------------------------------------ Student ------------------------------------------------------------------------------ //

function usercheck_init_student(i, userid = '', profileid) {
    var obj = document.getElementById('ajax_output_st');
    obj.innerHTML = '';

    if (i.value.length < 1)
        return;

    var err = new Array();
    if (i.value.match(/[^A-Za-z0-9_@.]/))
        err[err.length] = 'Username can only contain letters, numbers, underscores, at the rate and dots';
    if (i.value.length < 3)
        err[err.length] = 'Username Too Short';
    if (err != '') {
        obj.style.color = '#ff0000';
        obj.innerHTML = err.join('<br />');

        if(i.value.length > 1)
        {
            window.$("#stu_username_flag").val("0");
            window.$("#mod_student_btn").attr("disabled", true);
        }

        return;
    }
    window.$("#stu_username_flag").val("1");
    window.$("#mod_student_btn").attr("disabled", false);
    ajax_call('Validator.php?u=' + i.value + 'stud' + '&userid=' + userid + '&profileid=' + profileid, usercheck_callback_student, usercheck_error_student);
}

function usercheck_callback_student(data) {
    var response = data;

    var obj = document.getElementById('ajax_output_st');
    obj.style.color = (response == 1) ? '#008800' : '#ff0000';
    obj.innerHTML = (response == 1) ? 'Username Available' : 'Username already taken';
}

function usercheck_error_student(err) {
    alert("Error: " + err);
}


function usercheck_init_student_Mod(i, userid = '', profileid) {
    ajax_call('Validator.php?u=' + i.value + 'stud&userid=' + userid + '&profileid=' + profileid, usercheck_callback_student_Mod, usercheck_error_student_Mod);
}

function usercheck_callback_student_Mod(data) {
    var response = data;
    document.getElementById('ajax_output_st').innerHTML = '';
    if (response != 1)
    { 
        if (document.getElementById('students[USERNAME]')) {
            document.getElementById('students[USERNAME]').value = '';
        }
        else if (document.getElementById('inputstudents[USERNAME]')) {
            document.getElementById('inputstudents[USERNAME]').value = '';
        }

        if (document.getElementById('students[PASSWORD]')) {
            document.getElementById('students[PASSWORD]').value = '';
        }
        else if (document.getElementById('inputstudents[PASSWORD]')) {
            document.getElementById('inputstudents[PASSWORD]').value = '';
        }
    }

    if (document.getElementById("students[USERNAME]")){
        var student_username = document.getElementById("students[USERNAME]").value;
    }
    else if (document.getElementById("inputstudents[USERNAME]")){
        var student_username = document.getElementById("inputstudents[USERNAME]").value;
    }

    var student_username_flag = document.getElementById("stu_username_flag").value;

    if(student_username != '' && student_username_flag == '0')
    {
        var obj = document.getElementById('ajax_output_st');
        obj.style.color = '#ff0000';
        obj.innerHTML = 'Username can only contain letters, numbers, underscores, at the rate and dots';

        window.$("#mod_student_btn").attr("disabled", true);
    }
    else
    {
        window.$("#mod_student_btn").attr("disabled", false);
    }
}

function usercheck_error_student_Mod(err) {
    alert("Error: " + err);
}
// ------------------------------------------------------ Student ------------------------------------------------------------------------------ //

// ------------------------------------------------------ Student ID------------------------------------------------------------------------------ //

function usercheck_student_id(i) {
    var obj = document.getElementById('ajax_output_stid');
    obj.innerHTML = '';

    if (i.value.length < 1)
        return;

    var err = new Array();
    if (i.value.match(/[^0-9_]/))
        err[err.length] = 'Student ID can only contain numbers';

    if (err != '') {
        obj.style.color = '#ff0000';
        obj.innerHTML = err.join('<br />');
        return;
    }
    ajax_call('ValidatorInt.php?u=' + i.value + 'stid', usercheck_callback_student_id, usercheck_error_student_id);
}

function usercheck_callback_student_id(data) {
    var response = (data == '1');

    var obj = document.getElementById('ajax_output_stid');
    obj.style.color = (response) ? '#008800' : '#ff0000';
    obj.innerHTML = (response == '1') ? 'Student ID OK' : 'Student ID already taken';
}

function usercheck_error_student_id(err) {
    alert("Error: " + err);
}

// ------------------------------------------------------ Student ID------------------------------------------------------------------------------ //


//-----------------Take attn depends on period------------------------------------------------------
function disable_hidden_field(option, value)
{
    if (option == '')
        var ids = 'cp';
    else if (option == 1)
        var ids = '';
    else if (option == 2)
        var ids = document.getElementById('fixed_day').value;

    document.F2.fixed_hidden.disabled = true;

}
function formcheck_periods_attendance_F2(option, attendance, i_value)
{
    if (option == '')
        var ids = 'cp';
    else if (option == 1)
        var ids = '';
    else if (option == 2)
        var ids = document.getElementById('fixed_day').value;
    else if (option == 3)
    {
        if (i_value == '')
            var ids = document.getElementById('fixed_day3').value;
        else
            var ids = document.getElementById('fixed_day3_' + i_value).value;
    } else if (option == 4)
        var ids = document.getElementById('fixed_day4').value;
    else
        var ids = option;

    if (document.getElementById(ids + '_period'))
    {
        period_id = document.getElementById(ids + '_period').value;
    } else
    {
        if (option == 3)
        {
            if (document.getElementById('disabled_option_' + i_value).value)
                period_id = document.getElementById('disabled_option_' + i_value).value;
            else
                period_id = 0;
        } else
            period_id = 0;
    }

    var err = new Array();
    if (attendance.checked)
    {
        var obj = document.getElementById('ajax_output');
        var period_id;
        var cp_id = document.getElementById(ids + '_id').value;
        obj.innerHTML = '';
        if (attendance.value.length < 1)
            return;

        if (period_id.length == 0)
        {
            err[err.length] = 'Select Period';
            attendance.checked
            document.getElementById('get_status').value = 'false';
        } else
            err[err.length] = '';
        if (err != '') {
            obj.style.color = '#ff0000';
            obj.innerHTML = err.join('<br />');
            return;
        }
        var pqr = attendance.value;
        var att_id = attendance.id;
        ajax_call('ValidatorAttendance.php?u=' + attendance.value + '&p_id=' + period_id + '&cp_id=' + cp_id + '&ids=' + att_id, attendance_callback, attendance_error);
        var xmlhttp;
        if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari
            xmlhttp = new XMLHttpRequest();
        } else
        {// code for IE6, IE5
            xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange = function ()
        {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
            {
                var tag_id = xmlhttp.responseText;
                if (tag_id != '0' && tag_id != '1')
                {
                    if (document.getElementById(tag_id))
                        document.getElementById(tag_id).checked = false;
                }
            }
        }
        xmlhttp.open("GET", 'ValidatorAttendance.php?u=' + attendance.value + '&p_id=' + period_id + '&cp_id=' + cp_id + '&ids=' + att_id, true);
        xmlhttp.send();
    } else
    {
        if (period_id.length == 0)
        {
            err[err.length] = 'Select Period';
            document.getElementById('get_status').value = 'false';
        } else
            err[err.length] = '';
        if (err != '') {
            obj.style.color = '#ff0000';
            obj.innerHTML = err.join('<br />');
            return;
        }
        if (err == '')
        {
            document.getElementById('ajax_output').innerHTML = '';
            document.getElementById('get_status').value = '';
        }

        var cp_id = document.getElementById('hidden_cp_id').value;

        if (cp_id != 'new')
        {
            var schedule_type_id = document.getElementById('schedule_type_id').value;
            if (schedule_type_id == 'FIXED')
            {
                var child = document.getElementById("divtables[course_periods][" + cp_id + "][HALF_DAY]").children[0];
                var clickEvent = new MouseEvent("click");
                child.dispatchEvent(clickEvent);
                document.getElementById('half_day').disabled = true;
                document.getElementById('half_day').checked = false;
            }
            if (schedule_type_id == 'VARIABLE')
            {
                var does_attendance_ids = document.getElementById('does_attendance_ids').value;
                does_attendance_ids = does_attendance_ids.split(',');
                var go_dai = 'y';
                for (var dai = 0; dai <= does_attendance_ids.length; dai++)
                {
                    if (document.getElementById(does_attendance_ids[dai]))
                    {
                        if (document.getElementById(does_attendance_ids[dai]).checked == true)
                            go_dai = 'n';
                    }
                }
                if (go_dai == 'y')
                {
                    var child = document.getElementById("divtables[course_periods][" + cp_id + "][HALF_DAY]").children[0];
                    var clickEvent = new MouseEvent("click");
                    child.dispatchEvent(clickEvent);
                    document.getElementById('half_day').disabled = true;
                    document.getElementById('half_day').checked = false;
                }

            }


        } else
        {
            document.getElementById('half_day').disabled = true;
            document.getElementById('half_day').checked = false;
        }
    }
}

function attendance_callback(data)
{

    var response = data.split('/');
    var obj = document.getElementById('ajax_output');
    obj.style.color = (response[0] == 1) ? '#008800' : '#ff0000';
    obj.innerHTML = (response[0] == 1 ? '' : 'Turn on attendance for the<br>period in School Setup &gt;&gt; Periods');
    if (response[0] == 0)
    {
        document.getElementById('get_status').value = response;
        document.getElementById(response[1]).checked = false;
    } else
    {
        document.getElementById('get_status').value = '';


        var cp_id = document.getElementById('hidden_cp_id').value;
        if (cp_id != 'new')
        {
            var child = document.getElementById("divtables[course_periods][" + cp_id + "][HALF_DAY]").children[0];
            var clickEvent = new MouseEvent("click");
            child.dispatchEvent(clickEvent);
            document.getElementById('half_day').disabled = false;
        } else
        {
            if (document.getElementById('F_does_attendance'))
            {
                if (document.getElementById('F_does_attendance').checked == true)
                    document.getElementById('half_day').disabled = false;
            }
        }


    }
}

function attendance_error(err) {

}

function formcheck_periods_F2(option)
{
    if (document.getElementById('hidden_period_block'))
        document.getElementById('hidden_period_block').value = document.getElementById('_period').value;

    var ids = option.toString();
    if (!document.getElementById(ids + '_does_attendance') || (!document.getElementById(ids + '_does_attendance').checked))
    {
        var obj = document.getElementById('ajax_output');
        var period_id = document.getElementById(ids + '_period').value;
        var cp_id = document.getElementById(ids + '_id').value;
        var err = new Array();
        if (period_id.length == 0)
        {
            err[err.length] = 'Select Period';
            document.getElementById('get_status').value = 'false';
        } else
            err[err.length] = '';
        if (err == '')
        {
            document.getElementById('ajax_output').innerHTML = '';
            document.getElementById('get_status').value = '';
        }
        if (err != '')
        {
            obj.style.color = '#ff0000';
            obj.innerHTML = err.join('<br />');
            return;
        }
        if (!document.getElementById(ids + '_does_attendance'))
            ajax_call('ValidatorAttendance.php?u=N&p_id=' + period_id + '&cp_id=' + cp_id, attendance_callback, attendance_error);
    } else
    {
        if (document.getElementById(ids + '_does_attendance').checked)
        {
            formcheck_periods_attendance_F2(document.getElementById(ids + '_does_attendance'));
        } else
            document.getElementById('get_status').value = '';
    }
}

//----------------------------------------------------------------------


function ajax_call_modified(url, callback_function, error_function)
{
    var xmlHttp = null;
    try {
        xmlHttp = new XMLHttpRequest();
    } catch (e) {
        try {
            xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 1) {
            try {

                if (url == 'BackupForRollover.php?action=Backup')
                    document.getElementById('back_db').style.display = "block";
                else
                    document.getElementById('calculating').style.display = "block";
            } catch (e) {
                error_function(e.description);
            }
        }
        if (xmlHttp.readyState == 4) {
            try {
                if (xmlHttp.status == 200) {
                    callback_function(xmlHttp.responseText);
                }
            } catch (e) {
                error_function(e.description);
            }
        }
    }


    xmlHttp.open("GET", url);
    xmlHttp.send(null);
}

function ajax_call_modified_forgotpass(url, callback_function, error_function, div_id)
{
    var xmlHttp = null;
    try {
        xmlHttp = new XMLHttpRequest();
    } catch (e) {
        try {
            xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 1) {
            try {
                document.getElementById(div_id).style.display = "block";
            } catch (e) {
                error_function(e.description);
            }
        }
        if (xmlHttp.readyState == 4) {
            try {
                if (xmlHttp.status == 200) {
                    callback_function(xmlHttp.responseText);
                }
            } catch (e) {
                error_function(e.description);
            }
        }
    }
    xmlHttp.open("GET", url);
    xmlHttp.send(null);
}

//=========================================Missing Attendance===========================
function mi_callback(mi_data)
{
    document.getElementById("resp").innerHTML = mi_data;
    document.getElementById("calculating").style.display = "none";
    if (mi_data.search('NEW_MI_YES') != -1)
    {
        document.getElementById("attn_alert").style.display = "block"
    }
}
function calculate_missing_atten()
{
    var url = "CalculateMissingAttendance.php";
    ajax_call_modified(url, mi_callback, missing_attn_error);
}

function missing_attn_error(err)
{
    alert("Error: " + err);
}
//-------------------------------------Missing Attendance end ------------------------------------------------

function recalculate_gpa(stu_all, mp)
{
    var url = 'RecalCulateProcess.php?students=' + stu_all + '&mp=' + mp;
    ajax_call_modified(url, re_gpa_callback, recal_gpa_error);
}
function re_gpa_callback(re_gpa_data)
{
    document.getElementById("resp").innerHTML = re_gpa_data;
    document.getElementById("calculating").style.display = "none";

}
function recal_gpa_error(err)
{
    alert("Error: " + err);
}


function calculate_gpa(mp)
{
    var url = 'CalculateGpaProcess.php?&mp=' + mp;
    ajax_call_modified(url, gpa_callback, gpa_error);
}
function gpa_callback(re_gpa_data)
{

    document.getElementById("resp").innerHTML = re_gpa_data;
    document.getElementById("calculating").style.display = "none";

}
function gpa_error(err)
{
    alert("Error: " + err);
}



function rollover_callback(roll_data)
{

    roll_data = roll_data.trim();
    var total_data;
    total_data = roll_data.split('|');
    var value = total_data[2];
    if (value == 0)
    {
        var rollover_class = 'rollover_no';
    } else
    {
        var rollover_class = 'rollover_yes';
    }

    if (total_data[0] == 'users') {
        document.getElementById("staff").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("staff").setAttribute("class", rollover_class);
        document.getElementById("staff").setAttribute("className", rollover_class);
        if (document.getElementById("chk_school_periods").value == 'Y')
        {
            ajax_rollover('school_periods');
        } else
        {
            ajax_rollover('school_years');
        }
    } else if (total_data[0] == 'School Periods')
    {
        document.getElementById("school_periods").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("school_periods").setAttribute("class", rollover_class);
        document.getElementById("school_periods").setAttribute("className", rollover_class);
        ajax_rollover('school_years');
    } else if (total_data[0] == 'Marking Periods')
    {
        document.getElementById("school_years").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("school_years").setAttribute("class", rollover_class);
        document.getElementById("school_years").setAttribute("className", rollover_class);

        if (document.getElementById("chk_school_calendars").value == 'Y')
        {
            ajax_rollover('school_calendars');
        } else if (document.getElementById("chk_report_card_grade_scales").value == 'Y')
        {
            ajax_rollover('report_card_grade_scales');
        } else if (document.getElementById("chk_course_subjects").value == 'Y')
        {
            ajax_rollover('course_subjects');
        } else if (document.getElementById("chk_courses").value == 'Y')
        {
            ajax_rollover('courses');
        } else if (document.getElementById("chk_course_periods").value == 'Y')
        {
            ajax_rollover('course_periods');
        } else
        {
            ajax_rollover('student_enrollment_codes');
        }

    } else if (total_data[0] == 'Calendars')
    {
        document.getElementById("attendance_calendars").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("attendance_calendars").setAttribute("class", rollover_class);
        document.getElementById("attendance_calendars").setAttribute("className", rollover_class);
        ajax_rollover('report_card_grade_scales');
    } else if (total_data[0] == 'Report Card Grade Codes')
    {
        document.getElementById("report_card_grade_scales").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("report_card_grade_scales").setAttribute("class", rollover_class);
        document.getElementById("report_card_grade_scales").setAttribute("className", rollover_class);
        if (document.getElementById('chk_course_subjects').value == 'Y')
            ajax_rollover('course_subjects');
        else if (document.getElementById('chk_courses').value == 'Y')
            ajax_rollover('courses');
        else if (document.getElementById('chk_course_periods').value == 'Y')
            ajax_rollover('course_periods');
        else
            ajax_rollover('student_enrollment_codes');
    } else if (total_data[0] == 'Subjects')
    {
        document.getElementById("course_subjects").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("course_subjects").setAttribute("class", rollover_class);
        document.getElementById("course_subjects").setAttribute("className", rollover_class);
        if (document.getElementById('chk_courses').value == 'Y')
            ajax_rollover('courses');
        else if (document.getElementById('chk_course_periods').value == 'Y')
            ajax_rollover('course_periods');
        else
            ajax_rollover('student_enrollment_codes');
    } else if (total_data[0] == 'Courses')
    {
        document.getElementById("courses").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("courses").setAttribute("class", rollover_class);
        document.getElementById("courses").setAttribute("className", rollover_class);
        if (document.getElementById('chk_course_periods').value == 'Y')
            ajax_rollover('course_periods');
        else
            ajax_rollover('student_enrollment_codes');
    } else if (total_data[0] == 'Course Periods')
    {
        document.getElementById("course_periods").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("course_periods").setAttribute("class", rollover_class);
        document.getElementById("course_periods").setAttribute("className", rollover_class);
        ajax_rollover('student_enrollment_codes');
    } else if (total_data[0] == 'Student Enrollment Codes')
    {
        document.getElementById("student_enrollment_codes").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("student_enrollment_codes").setAttribute("class", rollover_class);
        document.getElementById("student_enrollment_codes").setAttribute("className", rollover_class);
        ajax_rollover('student_enrollment');
    } else if (total_data[0] == 'Students')
    {
        document.getElementById("student_enrollment").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("student_enrollment").setAttribute("class", rollover_class);
        document.getElementById("student_enrollment").setAttribute("className", rollover_class);
        if (document.getElementById("chk_honor_roll").value == 'Y')
        {
            ajax_rollover('honor_roll');
        } else if (document.getElementById("chk_attendance_codes").value == 'Y')
        {
            ajax_rollover('attendance_codes');
        } else if (document.getElementById("chk_report_card_comments").value == 'Y')
        {
            ajax_rollover('report_card_comments');
        } else
        {
            ajax_rollover('NONE');
        }
    } else if (total_data[0] == 'Honor Roll Setup')
    {
        document.getElementById("honor_roll").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("honor_roll").setAttribute("class", rollover_class);
        document.getElementById("honor_roll").setAttribute("className", rollover_class);
        if (document.getElementById("chk_attendance_codes").value == 'Y')
        {
            ajax_rollover('attendance_codes');
        } else if (document.getElementById("chk_report_card_comments").value == 'Y')
        {
            ajax_rollover('report_card_comments');
        } else
        {
            ajax_rollover('NONE');
        }

    } else if (total_data[0] == 'Attendance Codes')
    {
        document.getElementById("attendance_codes").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("attendance_codes").setAttribute("class", rollover_class);
        document.getElementById("attendance_codes").setAttribute("className", rollover_class);

        if (document.getElementById("chk_report_card_comments").value == 'Y')
        {
            ajax_rollover('report_card_comments');
        } else
        {
            ajax_rollover('NONE');
        }
    } else if (total_data[0] == 'Report Card Comment Codes')
    {
        document.getElementById("report_card_comments").innerHTML = total_data[4] + " " + total_data[1] + " " + total_data[2] + " " + total_data[3];
        document.getElementById("report_card_comments").setAttribute("class", rollover_class);
        document.getElementById("report_card_comments").setAttribute("className", rollover_class);
        ajax_rollover('NONE');
    } else
    {
        document.getElementById("response").innerHTML = roll_data;
        document.getElementById("calculating").style.display = "none";
    }
}

function rollover_error(err)
{
    alert("Error: " + err);
}
function back_before_roll()
{

    var url = 'BackupForRollover.php?action=Backup';
    ajax_call_modified(url, back_before_roll_callback, back_before_roll_error);

}
function back_before_roll_callback(data)
{
    if (data.trim() == 'File Saved')
    {
        document.getElementById('back_db').style.display = "none";
        ajax_rollover('staff');
    } else
    {
        alert('Error: ' + data);
    }
}
function back_before_roll_error(err)
{
    alert("Error: " + err);
}
function ajax_rollover(roll_table)
{
    var url = 'RolloverShadow.php?table_name=' + roll_table;
    ajax_call_modified(url, rollover_callback, rollover_error);
}
function formcheck_rollover()
{
    var start_month_len = document.getElementById("monthSelect1").value;
    var start_day_len = document.getElementById("daySelect1").value;
    var start_year_len = document.getElementById("yearSelect1").value;
    if (start_month_len == "" || start_day_len == "" || start_year_len == "")
    {
        document.getElementById("start_date").innerHTML = "Please Enter Start Date ";
        return false;
    }

    var custom_dt = document.getElementById("custom_date").value;
    if (custom_dt == "Y")
    {
        var prev_end_date_s = document.getElementById("prev_start_date").value;
        var prev_end_date = Date.parse(prev_end_date_s);
        var s_month_len = document.getElementById("monthSelect2").value;
        var s_day_len = document.getElementById("daySelect2").value;
        var s_year_len = document.getElementById("yearSelect2").value;

        var e_month_len = document.getElementById("monthSelect3").value;
        var e_day_len = document.getElementById("daySelect3").value;
        var e_year_len = document.getElementById("yearSelect3").value;

        if (s_month_len == "" || s_day_len == "" || s_year_len == "")
        {
            document.getElementById("start_date").innerHTML = "Please Enter a Valid New Year's Begin Date";
            return false;
        }
        if (e_month_len == "" || e_day_len == "" || e_year_len == "")
        {
            document.getElementById("start_date").innerHTML = "Please Enter Valid New Year's End Date";
            return false;
        }
        if (s_month_len != "" && s_day_len != "" && s_year_len != "" && e_month_len != "" && e_day_len != "" && e_year_len != "")
        {
            var s_start_s = s_year_len + '-' + s_month_len + '-' + s_day_len;
            var e_start_s = e_year_len + '-' + e_month_len + '-' + e_day_len;
            var s_start_dt = Date.parse(s_start_s);
            var s_end_dt = Date.parse(e_start_s);
            if (s_start_dt <= prev_end_date)
            {
                document.getElementById("start_date").innerHTML = "New Year's Begin Date Has To Be After Previous Year's End Date";
                return false;
            } else if (s_start_dt >= s_end_dt)
            {
                document.getElementById("start_date").innerHTML = "New Year's End Date Has To Be After New Year's Start Date";
                return false;
            } else
            {
                var tot_round = document.getElementById("tot_round");

                if (tot_round != null)
                {
                    tot_round = tot_round.value;
                    tot_round = parseInt(tot_round);
                    var prev_l_st = 0;
                    for (var i = 1; i <= tot_round; i++)
                    {
                        var l_st = document.getElementById("round_" + i).value;
                        l_st = parseInt(l_st);
                        var l_st_m = l_st - 1;
                        var l_en = document.getElementById("roll_" + i).value;
                        l_en = parseInt(l_en);
                        ///////Checking semesters////////////////////////
                        for (var j = l_st_m; j <= l_st; j++)
                        {
                            var s_month = document.getElementById("monthSelect" + j).value;
                            var s_day = document.getElementById("daySelect" + j).value;
                            var s_year = document.getElementById("yearSelect" + j).value;
                            var sem_dt = s_year + '-' + s_month + '-' + s_day;
                            var sem_name = document.getElementById("name_" + j).value;
                            if (s_month == "" || s_day == "" || s_year == "")
                            {
                                document.getElementById("start_date").innerHTML = "Please Enter Valid " + sem_name;
                                return false;
                            } else
                            {
                                sem_dt = Date.parse(sem_dt);
                                if (sem_dt < s_start_dt)
                                {
                                    document.getElementById("start_date").innerHTML = sem_name + " Cannot Be Before School's Begin Date";
                                    return false;
                                }
                                if (sem_dt > s_end_dt)
                                {
                                    document.getElementById("start_date").innerHTML = sem_name + " Cannot Be Be After School's End Date";
                                    return false;
                                } else
                                {

                                    if (j != l_st_m)
                                    {
                                        var j_p = j - 1;
                                        var s_p_month = document.getElementById("monthSelect" + j_p).value;
                                        var s_p_day = document.getElementById("daySelect" + j_p).value;
                                        var s_p_year = document.getElementById("yearSelect" + j_p).value;
                                        var sem_p_dt = s_p_year + '-' + s_p_month + '-' + s_p_day;
                                        sem_p_dt = Date.parse(sem_p_dt);
                                        var sem_p_name = document.getElementById("name_" + j_p).value;
                                        if (sem_dt < sem_p_dt)
                                        {
                                            document.getElementById("start_date").innerHTML = sem_name + " Cannot Be Before " + sem_p_name;
                                            return false;
                                        }
                                    } else
                                    {
                                        if (prev_l_st != 0)
                                        {
                                            var p_e_month = document.getElementById("monthSelect" + prev_l_st).value;
                                            var p_e_day = document.getElementById("daySelect" + prev_l_st).value;
                                            var p_e_year = document.getElementById("yearSelect" + prev_l_st).value;
                                            var e_p_dt = p_e_year + '-' + p_e_month + '-' + p_e_day;
                                            e_p_dt = Date.parse(e_p_dt);
                                            var e_p_name = document.getElementById("name_" + prev_l_st).value;
                                            if (sem_dt < e_p_dt)
                                            {
                                                document.getElementById("start_date").innerHTML = sem_name + " Cannot Be Before " + e_p_name;
                                                return false;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        var check_q = document.getElementById("quarter_" + i);
                        if (check_q != null)
                        {
                            check_q = check_q.value;
                            if (check_q != '')
                            {
                                var q_da = check_q.split("-");
                                for (var d_q = 0; d_q < q_da.length; d_q++)
                                {
                                    var q_t = q_da[d_q];
                                    var t_q = q_t.split("`");
                                    var qs_month = document.getElementById("monthSelect" + t_q[0]).value;
                                    var qs_day = document.getElementById("daySelect" + t_q[0]).value;
                                    var qs_year = document.getElementById("yearSelect" + t_q[0]).value;
                                    var qs_dt = qs_year + '-' + qs_month + '-' + qs_day;

                                    qs_dt = Date.parse(qs_dt);
                                    var qs_name = document.getElementById("name_" + t_q[0]).value;

                                    var qe_month = document.getElementById("monthSelect" + t_q[1]).value;
                                    var qe_day = document.getElementById("daySelect" + t_q[1]).value;
                                    var qe_year = document.getElementById("yearSelect" + t_q[1]).value;
                                    var qe_dt = qe_year + '-' + qe_month + '-' + qe_day;
                                    qe_dt = Date.parse(qe_dt);
                                    var qe_name = document.getElementById("name_" + t_q[1]).value;

                                    var ss_month = document.getElementById("monthSelect" + l_st_m).value;
                                    var ss_day = document.getElementById("daySelect" + l_st_m).value;
                                    var ss_year = document.getElementById("yearSelect" + l_st_m).value;
                                    var ss_dt = ss_year + '-' + ss_month + '-' + ss_day;
                                    ss_dt = Date.parse(ss_dt);
                                    var ss_name = document.getElementById("name_" + l_st_m).value;

                                    var se_month = document.getElementById("monthSelect" + l_st).value;
                                    var se_day = document.getElementById("daySelect" + l_st).value;
                                    var se_year = document.getElementById("yearSelect" + l_st).value;
                                    var se_dt = se_year + '-' + se_month + '-' + se_day;
                                    se_dt = Date.parse(se_dt);
                                    var se_name = document.getElementById("name_" + l_st).value;

                                    if (qs_month == "" || qs_day == "" || qs_year == "")
                                    {
                                        document.getElementById("start_date").innerHTML = "Please Enter Valid " + qs_name;
                                        return false;
                                    }
                                    if (qe_month == "" || qe_day == "" || qe_year == "")
                                    {
                                        document.getElementById("start_date").innerHTML = "Please Enter Valid " + qe_name;
                                        return false;
                                    }

                                    if (qs_month != "" && qs_day != "" && qs_year != "")
                                    {
                                        if (qs_dt < ss_dt)
                                        {
                                            document.getElementById("start_date").innerHTML = qs_name + " Cannot Be Before " + ss_name;
                                            return false;
                                        }
                                        if (qs_dt > se_dt)
                                        {
                                            document.getElementById("start_date").innerHTML = qs_name + " Cannot Be After " + se_name;
                                            return false;
                                        }
                                    }
                                    if (qe_month != "" && qe_day != "" && qe_year != "")
                                    {
                                        if (qe_dt < qs_dt)
                                        {
                                            document.getElementById("start_date").innerHTML = qe_name + " Cannot Be Before " + qs_name;
                                            return false;
                                        }
                                        if (qe_dt > se_dt)
                                        {
                                            document.getElementById("start_date").innerHTML = qe_name + " Cannot Be After " + se_name;
                                            return false;
                                        }
                                    }

                                    if (d_q != 0)
                                    {
                                        var pd_q = d_q - 1;
                                        var old_elem = q_da[pd_q];
                                        var s_old_elem = old_elem.split('`');
                                        var qp_month = document.getElementById("monthSelect" + s_old_elem[1]).value;
                                        var qp_day = document.getElementById("daySelect" + s_old_elem[1]).value;
                                        var qp_year = document.getElementById("yearSelect" + s_old_elem[1]).value;
                                        var qp_dt = qp_year + '-' + qp_month + '-' + qp_day;

                                        qp_dt = Date.parse(qp_dt);
                                        var qp_name = document.getElementById("name_" + s_old_elem[1]).value;
                                        if (qp_dt > qs_dt)
                                        {
                                            document.getElementById("start_date").innerHTML = qs_name + " Cannot Be Before " + qp_name;
                                            return false;
                                        }
                                    }
                                    var check_p = document.getElementById("progress_" + i);
                                    if (check_p != null)
                                    {
                                        check_p = check_p.value;
                                        if (check_p != '')
                                        {
                                            var p_da = check_p.split("-");

                                            var check_c_p = p_da[d_q].split('^');
                                            for (var ip = 0; ip < check_c_p.length; ip++)
                                            {
                                                var m_p = check_c_p[ip].split('`');
                                                var ps_month = document.getElementById("monthSelect" + m_p[0]).value;
                                                var ps_day = document.getElementById("daySelect" + m_p[0]).value;
                                                var ps_year = document.getElementById("yearSelect" + m_p[0]).value;
                                                var ps_dt = ps_year + '-' + ps_month + '-' + ps_day;

                                                ps_dt = Date.parse(ps_dt);
                                                var ps_name = document.getElementById("name_" + m_p[0]).value;

                                                var pe_month = document.getElementById("monthSelect" + m_p[1]).value;
                                                var pe_day = document.getElementById("daySelect" + m_p[1]).value;
                                                var pe_year = document.getElementById("yearSelect" + m_p[1]).value;
                                                var pe_dt = pe_year + '-' + pe_month + '-' + pe_day;
                                                pe_dt = Date.parse(pe_dt);
                                                var pe_name = document.getElementById("name_" + m_p[1]).value;

                                                if (ps_month == '' || ps_day == '' || ps_year == '')
                                                {
                                                    document.getElementById("start_date").innerHTML = "Please Enter Valid " + ps_name;
                                                    return false;
                                                }
                                                if (pe_month == '' || pe_day == '' || pe_year == '')
                                                {
                                                    document.getElementById("start_date").innerHTML = "Please Enter Valid " + pe_name;
                                                    return false;
                                                }
                                                if (ps_month != "" && ps_day != "" && ps_year != "")
                                                {
                                                    if (ps_dt < qs_dt)
                                                    {
                                                        document.getElementById("start_date").innerHTML = ps_name + " Cannot Be Before " + qs_name;
                                                        return false;
                                                    }
                                                    if (ps_dt > qe_dt)
                                                    {
                                                        document.getElementById("start_date").innerHTML = ps_name + " Cannot Be After " + qe_name;
                                                        return false;
                                                    }
                                                }
                                                if (pe_month != "" && pe_day != "" && pe_year != "")
                                                {
                                                    if (pe_dt < ps_dt)
                                                    {
                                                        document.getElementById("start_date").innerHTML = pe_name + " Cannot Be Before " + ps_name;
                                                        return false;
                                                    }
                                                    if (pe_dt > qe_dt)
                                                    {
                                                        document.getElementById("start_date").innerHTML = pe_name + " Cannot Be After " + qe_name;
                                                        return false;
                                                    }
                                                }
                                                if (ip != 0)
                                                {
                                                    var pd_p = ip - 1;
                                                    var old_elem_p = check_c_p[pd_p];
                                                    var p_old_elem = old_elem_p.split('`');
                                                    var pp_month = document.getElementById("monthSelect" + p_old_elem[1]).value;
                                                    var pp_day = document.getElementById("daySelect" + p_old_elem[1]).value;
                                                    var pp_year = document.getElementById("yearSelect" + p_old_elem[1]).value;
                                                    var pp_dt = pp_year + '-' + pp_month + '-' + pp_day;

                                                    pp_dt = Date.parse(pp_dt);
                                                    var pp_name = document.getElementById("name_" + p_old_elem[1]).value;
                                                    if (pp_dt > ps_dt)
                                                    {
                                                        document.getElementById("start_date").innerHTML = ps_name + " Cannot Be Before " + pp_name;
                                                        return false;
                                                    }
                                                }

                                            }
                                        }
                                    }
                                }
                            }
                        }
                        prev_l_st = l_st;
                    }

                }
            }

        }


    }
}

function validate_rollover(thisFrm, thisElement)
{
    if (thisElement.name == 'courses')
    {
        if (thisElement.checked == true)
        {
            thisFrm.course_subjects.checked = true;
        }
    }

    if (thisElement.name == 'course_periods')
    {
        if (thisElement.checked == true)
        {
            thisFrm.school_periods.checked = true;
            thisFrm.attendance_calendars.checked = true;
            thisFrm.course_subjects.checked = true;
            thisFrm.courses.checked = true;

        }
        if (thisFrm.report_card_comments.checked == true && thisElement.checked == false)
        {
            thisElement.checked = true;
        }
    }
    if (thisElement.name == 'report_card_comments' && thisElement.checked == true)
    {
        thisFrm.school_periods.checked = true;
        thisFrm.attendance_calendars.checked = true;
        thisFrm.course_subjects.checked = true;
        thisFrm.courses.checked = true;
        thisFrm.course_periods.checked = true;
    }
    if (thisFrm.course_periods.checked == true && thisElement.checked == false && (thisElement.name == 'school_periods' || thisElement.name == 'attendance_calendars' || thisElement.name == 'course_subjects' || thisElement.name == 'courses'))
    {
        thisElement.checked = true;
    }
    if (thisFrm.courses.checked == true && thisElement.checked == false && thisElement.name == 'course_subjects')
    {
        thisElement.checked = true;
    }
}

function validate_password(password, stid)
{

    var url = "Validator.php?validate=pass&password=" + password + "&stfid=" + stid;
    ajax_call(url, pass_val_callback, pass_val_error);
}
function validate_password_staff(password, stid)
{

    var url = "Validator.php?validate=pass&password=" + password + "&stfid=" + stid;
    ajax_call(url, pass_val_callback_staff, pass_val_error);
}

function pass_val_callback(data)
{
    var obj = document.getElementById('passwordStrength');

    if (data != 1)
    {
        obj.style.color = '#ff0000';
        obj.style.backgroundColor = "#cccccc";
        obj.innerHTML = 'Invalid password';

    }

}
function pass_val_callback_staff(data)
{
    var obj = document.getElementById('passwordStrength');

    if (data != 1)
    {
        obj.style.color = '#ff0000';
        obj.style.backgroundColor = "#cccccc";
        obj.innerHTML = 'Invalid password';
        document.getElementById('PASSWORD').value = '';
    }

}
function pass_val_error(err)
{
    alert("Error: " + err);
}

function validate_password_mod(password, opt)
{
    document.getElementById('val_pass').value = 'Y';
    var url = "Validator.php?validate=pass_o&password=" + password + "&opt=" + opt;
    ajax_call(url, pass_val_callback_mod, pass_val_error);

}

function pass_val_callback_mod(data)
{

    var data_m = data.split("_");
    data = data_m[0];
    var obj = document.getElementById('passwordStrength' + data_m[1]);

    if (data != '1')
    {
        obj.style.color = '#ff0000';
        obj.style.backgroundColor = "#cccccc";
        obj.innerHTML = 'Invalid password';
        document.getElementById('val_pass').value = '';
    }

}

function pass_val_error(err)
{
    alert("Error: " + err);
}



//-------------------------------------------------- historical grade school name pickup --------------------------------------//
function pick_schoolname(data) {

    document.getElementById('SCHOOL_NAME').value = data;
}

// ------------------------------------------------------ Student ------------------------------------------------------------------------------ //

// ------------------------------------------------------ Student ID------------------------------------------------------------------------------ //

function GetSchool(i) {
    var obj = document.getElementById('SCHOOL_NAME');
    obj.innerHTML = '';

    ajax_call('GetSchool.php?u=' + i, pick_schoolname);
}
function show_cp_meeting_days(sch_type, cp_id)
{
    var cal_id = document.getElementById('calendar_id').value;
    document.getElementById("save_cp").style.display = "block";
    document.getElementById("save_cps").style.display = "block";
    if (cal_id != '' || sch_type == 'blocked')
    {
        if (sch_type == 'blocked')
        {
            document.getElementById("save_cp").style.display = "none";
            document.getElementById("save_cps").style.display = "none";
        }
        ajax_call('modules/schoolsetup/CourseProcess.php?task=md&cal_id=' + cal_id + '&cp_id=' + cp_id + '&sch_type=' + sch_type, meeting_days_callback, meeting_days_error);
    } else
    {
        document.getElementById('meeting_days').innerHTML = '<font color=red>Please select calendar</font>';
        document.getElementById('calendar_id').focus();

    }
}
function show_cp_meeting_daysError(sch_type, cp_id, cal_id, room_id, period_id, days, does_attendance)
{

    document.getElementById("save_cp").style.display = "block";
    document.getElementById("save_cps").style.display = "block";
    if (cal_id != '' || sch_type == 'blocked')
    {
        if (sch_type == 'blocked')
        {
            document.getElementById("save_cp").style.display = "none";
            document.getElementById("save_cps").style.display = "none";
        }
        ajax_call('modules/schoolsetup/CourseProcess.php?task=md&cal_id=' + cal_id + '&cp_id=' + cp_id + '&sch_type=' + sch_type + '&room_id=' + room_id + '&period_id=' + period_id + '&days=' + days + '&does_attendance=' + does_attendance + '&msg=conflict', meeting_days_callback, meeting_days_error);
    } else
    {
        document.getElementById('meeting_days').innerHTML = '<font color=red>Please select calendar</font>';
        document.getElementById('calendar_id').focus();

    }
}
function meeting_days_callback(data)
{
    document.getElementById('meeting_days').innerHTML = data;

}
function meeting_days_error(err)
{
    alert('Error ' + err)
}

function show_period_time(period_id, day, cp_id, cp_var_id)
{
    //document.getElementById(day+'_does_attendance').checked=false;  

    var cp_id = document.getElementById('hidden_cp_id').value;

    if (cp_id != 'new')
    {
        var schedule_type_id = document.getElementById('schedule_type_id').value;

        if (schedule_type_id == 'FIXED')
        {
            var child = document.getElementById("divtables[course_periods][" + cp_id + "][HALF_DAY]").children[0];
            var clickEvent = new MouseEvent("click");
            child.dispatchEvent(clickEvent);
            document.getElementById('half_day').disabled = true;
            document.getElementById('half_day').checked = false;
        }
        if (schedule_type_id == 'VARIABLE')
        {
            var does_attendance_ids = document.getElementById('does_attendance_ids').value;
            does_attendance_ids = does_attendance_ids.split(',');

            var go_dai = 'y';
            for (var dai = 0; dai <= does_attendance_ids.length; dai++)
            {
                if (document.getElementById(does_attendance_ids[dai]))
                {
                    if (document.getElementById(does_attendance_ids[dai]).checked == true)
                        go_dai = 'n';
                }
            }
            if (go_dai == 'y')
            {
                var child = document.getElementById("divtables[course_periods][" + cp_id + "][HALF_DAY]").children[0];
                var clickEvent = new MouseEvent("click");
                child.dispatchEvent(clickEvent);
                document.getElementById('half_day').disabled = true;
                document.getElementById('half_day').checked = false;
            }

        }


    } else
    {
        document.getElementById('half_day').disabled = true;
        document.getElementById('half_day').checked = false;
    }




    if (cp_var_id == 'n')
        cp_var_id = '';
    else if (cp_var_id == 'new')
        cp_var_id = 'new';
    if (period_id != '')
        ajax_call('modules/schoolsetup/CourseProcess.php?task=per_time&period_id=' + period_id + '&day=' + day + '&cp_id=' + cp_id + '&cp_var_id=' + cp_var_id, period_time_callback, period_time_error);
    else
        document.getElementById(day + '_period_time').innerHTML = '';
}

function period_time_callback(data)
{

    var n = data.indexOf("/");
    var id = data.substr(0, n).trim() + '_period_time';

    document.getElementById(id).innerHTML = data.substr(n + 1);

}
function period_time_error()
{

}

function verify_schedule(thisform)
{

    if (thisform.checked == false)
    {

        // document.getElementById('selected_' + thisform.id).innerHTML='';
//        if (document.getElementById('selected_course_' + thisform.value))
//        {
//            document.getElementById('selected_course_' + thisform.value).checked = false;
        var row = document.getElementById('selected_' + thisform.id);

        row.parentNode.removeChild(row);
//
//        }
    }
    ajax_call_modified('modules/scheduling/ScheduleProcess.php?cp_id=' + thisform.value + '&insert=' + thisform.checked, verify_schedule_callback, verify_schedule_error);
}

function verify_schedule_callback(data)
{
    if (data.indexOf("||") > 0)
    {
        var tdata = data.split("||");
        data = tdata[2];
        cp_id = tdata[0].trim();
    }
    data = data.trim();
    var stat = data.substr(0, 4);
    data = data.substr(4);
    document.getElementById("calculating").style.display = 'none';
    if (stat == 'resp')
    {
        $('#sub_btn').show();
        document.getElementById('selected_course1').innerHTML = document.getElementById('selected_course1').innerHTML + '<label class="checkbox-inline checkbox-switch switch-success switch-xs" id="selected_course_' + cp_id + '"><INPUT type="checkbox" id="selected_course_' + cp_id + '" name="selected_course_periods[]" checked="checked" value=' + cp_id + ' ><span></span>' + tdata[1] + '</label>';
        document.getElementById('conf_div1').innerHTML = '';
        document.getElementById('course_' + cp_id).checked = true;
//          document.getElementById('selected_course').innerHTML= '<tr id="selected_course_tr_'+cp_id+'"><td align=left><INPUT type="checkbox" id="selected_course_'+cp_id+'" name="selected_course_periods[]" checked="checked" ></td><td><b> aaaa</b></td></tr>';

        // document.getElementById('resp_table').innerHTML += data;
    } else if (stat == 'conf')
    {

        document.getElementById('conf_div1').style.color = "red";
        document.getElementById('conf_div1').innerHTML = data;

        var cp_id = document.getElementById('conflicted_cp').value;
        document.getElementById('course_' + cp_id).checked = false;
    } else
    {
        document.getElementById('conf_div1').innerHTML = data;
    }
}
function verify_schedule_error()
{
    alert('Error ' + err);
}
function fill_hidden_field(id, value)
{
    var final_value = new Array();
    var temp_text;

    for (var i = 0; i < value.length; i++)
    {
        temp_text = value.substr(i, 1);
        if (temp_text != ' ')
            final_value[i] = temp_text;
        else
            final_value[i] = '+';
    }
    document.getElementById(id).value = final_value.join('');

}

function peoplecheck_email(i, opt, p_id)
{
    //var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (i.value != '')
    {
        if (opt == 2 && p_id == 0)
        {
            if (document.getElementById('values[people][PRIMARY][EMAIL]'))
                var pri_email = document.getElementById('values[people][PRIMARY][EMAIL]').value;
            else
                pri_email = '';
            if (pri_email == i.value)
                peoplecheck_email_callback('0_2');
            else
                ajax_call('EmailCheck.php?email=' + i.value + '&p_id=' + p_id + '&opt=' + opt, peoplecheck_email_callback, peoplecheck_email_error);
        }
        if (opt == 2 && p_id != 0)
        {
            if (document.getElementById('inputvalues[people][PRIMARY][EMAIL]'))
                pri_email = document.getElementById('inputvalues[people][PRIMARY][EMAIL]').value;
            else
                pri_email = '';
            if (pri_email == i.value)
            {
                peoplecheck_email_callback('0_2');
            } else
                ajax_call('EmailCheck.php?email=' + i.value + '&p_id=' + p_id + '&opt=' + opt, peoplecheck_email_callback, peoplecheck_email_error);
        }
        if (opt == 1 && p_id != 0)
        {
            if (document.getElementById('inputvalues[people][SECONDARY][EMAIL]'))
                var sec_email = document.getElementById('inputvalues[people][SECONDARY][EMAIL]').value;
            else
                sec_email = '';
            if (sec_email == i.value)
            {

                peoplecheck_email_callback('0_1');
            } else
                ajax_call('EmailCheck.php?email=' + i.value + '&p_id=' + p_id + '&opt=' + opt, peoplecheck_email_callback, peoplecheck_email_error);
        }
        if (opt == 1 && p_id == 0)
        {
            if (document.getElementById('values[people][SECONDARY][EMAIL]'))
                sec_email = document.getElementById('values[people][SECONDARY][EMAIL]').value;
            else
                sec_email = '';
            if (sec_email == i.value)
                peoplecheck_email_callback('0_1');
            else
                ajax_call('EmailCheck.php?email=' + i.value + '&p_id=' + p_id + '&opt=' + opt, peoplecheck_email_callback, peoplecheck_email_error);
        }
    } else if (i.value != '')
    {
        document.getElementById('val_email_' + opt).value = '';
        document.getElementById('email_' + opt).innerHTML = '';
    } else if (i.value == '')
    {
        document.getElementById('val_email_' + opt).value = '';
        document.getElementById('email_' + opt).innerHTML = '';
    }
}
function peoplecheck_email_callback(data) {
    var response = data.split('_');
    var obj = document.getElementById('email_' + response[1]);
    if (response[0].trim() == '0')
    {
        obj.style.color = '#ff0000';
        obj.innerHTML = 'Email already taken';
        document.getElementById('val_email_' + response[1]).value = '';
    } else
    {
        obj.style.color = '#008800';
        obj.innerHTML = 'Email available';
        document.getElementById('val_email_' + response[1]).value = 'Y';
    }
}

function peoplecheck_email_error(err)
{
    alert("Error: " + err);
}

function check_email(i, id, p_id)
{
    //var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (i.value != '')
        ajax_call('EmailCheckOthers.php?email=' + i.value + '&id=' + id + '&type=' + p_id, check_email_callback, check_email_error);
}
function check_email_callback(data) {

    var response = data.split('_');
    if (response[1] == 2)
        var email_id = document.getElementsByName("staff[EMAIL]")[0].id;
    else if (response[1] == 3)
        email_id = document.getElementsByName("students[EMAIL]")[0].id;
    else
        email_id = document.getElementsByName("people[EMAIL]")[0].id;
    var obj = document.getElementById('email_error');
    if (response[0].trim() == 1)
    {
        obj.style.color = '#ff0000';
        obj.innerHTML = 'Email already taken';
        document.getElementById(email_id).value = '';
    }

    if (response[0].trim() == 0)
    {
        obj.style.color = '#008800';
        obj.innerHTML = 'Email available';
    }
}

function check_email_error(err)
{
    // alert("Error: " + err);
}
function check_username_install(username)
{
    if (username != '' || username.toLowerCase() != 'os4ed')
        ajax_call('UsernameCheckOthers.php?email=' + username, check_username_install_callback, check_username_install_error);
}
function check_username_install_callback(data) {


    var obj = document.getElementById('ucheck');
    if (data == '1')
    {
        obj.style.color = '#ff0000';
        obj.innerHTML = 'Username already taken';
        document.getElementById(auname).value = '';
    }

    if (data == 0)
    {
        obj.style.color = '#008800';
        obj.innerHTML = 'Username available';
    }
}
function check_username_install_error(err)
{
    alert("Error: " + err);
}
function forgotpassemail_init(usr_type)
{
    var i;

    if (usr_type == 'pass_email')
        i = document.getElementById('password_stf_email');
    if (usr_type == 'uname_email')
        i = document.getElementById('username_stf_email');
    var pqr = i.value.trim();
    if (usr_type == 'pass_email' && document.getElementById('uname').value == '' && pqr != '')
    {
        document.getElementById('pass_err_msg_email').style.color = '#ff0000';
        document.getElementById('pass_err_msg_email').innerHTML = 'Please enter username.';
        document.getElementById('pass_email').value = '';
        return false;
    }
    //var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (i.value.length > 0)
    {


//            if (usr_type == 'pass_email')
//            {
//                document.getElementById('pass_err_msg_email').style.color = '#ff0000';
//                document.getElementById('pass_err_msg_email').innerHTML = 'Please enter proper email address.';
//                document.getElementById('pass_email').value = '';
//                return false;
//            }
//            else
//            {
//                document.getElementById('uname_err_msg_email').style.color = '#ff0000';
//                document.getElementById('uname_err_msg_email').innerHTML = 'Please enter proper email address.';
//                document.getElementById('un_email').value = '';
//                return false;
//            }


        if (usr_type == 'pass_email')
        {
            document.getElementById('pass_err_msg_email').innerHTML = '';
            var username = document.getElementById('uname').value;
            if (document.getElementById('pass_staff').checked == true)
                ajax_call_modified_forgotpass('ForgotPassUserName.php?username=' + username + '&u=' + pqr + '&user_type=staff&used_for=email&form=' + usr_type, forgotpassemail_callback, forgotpassemail_error, 'pass_calculating_email');
            else
                ajax_call_modified_forgotpass('ForgotPassUserName.php?username=' + username + '&u=' + pqr + '&user_type=parent&used_for=email&form=' + usr_type, forgotpassemail_callback, forgotpassemail_error, 'pass_calculating_email');
            return false;
        }
        if (usr_type == 'uname_email')
        {
            document.getElementById('uname_err_msg_email').innerHTML = '';
            if (document.getElementById('uname_staff').checked == true)
                ajax_call_modified_forgotpass('ForgotPassUserName.php?username=&u=' + pqr + '&user_type=staff&used_for=email&form=' + usr_type, forgotpassemail_callback, forgotpassemail_error, 'uname_calculating_email');
            else
                ajax_call_modified_forgotpass('ForgotPassUserName.php?username=&u=' + pqr + '&user_type=parent&used_for=email&form=' + usr_type, forgotpassemail_callback, forgotpassemail_error, 'uname_calculating_email');
            return false;
        }


    } else
    {
        document.getElementById('pass_err_msg_email').innerHTML = '';
        document.getElementById('uname_err_msg_email').innerHTML = '';
        document.getElementById('pass_email').value = '';
        document.getElementById('un_email').value = '';
        return true;
    }

}

function forgotpassemail_callback(data) {


    var response = data.split('~');
    var obj;
    if (response[1] == 'pass_email')
    {
        document.getElementById('pass_calculating_email').style.display = "none";
        obj = document.getElementById('pass_err_msg_email');
    } else
    {
        document.getElementById('uname_calculating_email').style.display = "none";
        obj = document.getElementById('uname_err_msg_email');
    }

    if (response[0] == 1)
    {
        obj.style.color = '#008800';

        obj.innerHTML = 'Email found.';
        if (response[1] == 'pass_email')
        {
            document.getElementById('divErr').innerHTML = '';
            document.getElementById('pass_email').value = response[0].trim();
            if (document.getElementById("valid_func").value == 'Y')
            {
                document.getElementById("valid_func").value = 'N';
                if (document.getElementById("pass_student").checked == true)
                {
                    if (document.getElementById("password_stn_id").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter student id.</b></font>';
                        document.getElementById("password_stn_id").focus();
                        return false;
                    } else if (document.getElementById("uname").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your username.</b></font>';
                        document.getElementById("uname").focus();
                        return false;
                    } else if (document.getElementById("monthSelect1").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter date of birth.</b></font>';
                        return false;
                    } else
                        document.getElementById('f1').submit();

                } else
                {
                    if (document.getElementById("uname").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your username.</b></font>';
                        document.getElementById('pass_err_msg_email').innerHTML = '';
                        document.getElementById("uname").focus();
                        return false;
                    } else if (document.getElementById("pass_email").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your email.</b></font>';
                        document.getElementById('pass_err_msg_email').innerHTML = '';
                        document.getElementById("pass_stf_email").focus();
                        return false;
                    } else if (document.getElementById("pass_email").value == '0')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Incorrect login credential.</b></font>';
                        document.getElementById('pass_err_msg_email').innerHTML = '';
                        document.getElementById("pass_stf_email").focus();
                        return false;
                    } else
                        document.getElementById('f1').submit();
                }

            } else
                return true;

        } else
        {
            document.getElementById('divErr').innerHTML = '';
            document.getElementById('un_email').value = response[0];
            if (document.getElementById("valid_func").value == 'Y')
            {
                document.getElementById("valid_func").value = 'N';

                if (document.getElementById("uname_student").checked == true)
                {
                    if (document.getElementById("username_stn_id").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter student id.</b></font>';
                        document.getElementById("username_stn_id").focus();
                        return false;
                    } else if (document.getElementById("pass").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your password.</b></font>';
                        document.getElementById("pass").focus();
                        return false;
                    } else if (document.getElementById("monthSelect2").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your date of birth.</b></font>';
                        return false;
                    } else
                        document.getElementById('f1').submit();

                } else
                {
                    if (document.getElementById("pass").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your password.</b></font>';
                        document.getElementById('uname_err_msg_email').innerHTML = '';
                        document.getElementById("pass").focus();
                        return false;
                    } else if (document.getElementById("un_email").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your email.</b></font>';
                        document.getElementById('uname_err_msg_email').innerHTML = '';
                        document.getElementById("username_stf_email").focus();
                        return false;
                    } else if (document.getElementById("un_email").value == '0')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Incorrect login credential.</b></font>';
                        document.getElementById('uname_err_msg_email').innerHTML = '';
                        document.getElementById("username_stf_email").focus();
                        return false;
                    } else
                        document.getElementById('f1').submit();
                }
            }
        }

    } else
    {
        obj.style.color = '#ff0000';

        obj.innerHTML = 'Email not found.';
        if (response[1] == 'pass_email')
        {

            document.getElementById('pass_email').value = response[0];
            document.getElementById('divErr').innerHTML = '';
            if (document.getElementById("valid_func").value == 'Y')
            {
                document.getElementById("valid_func").value = 'N';
                if (document.getElementById("pass_student").checked == true)
                {
                    if (document.getElementById("password_stn_id").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter student id.</b></font>';
                        document.getElementById("password_stn_id").focus();
                        return false;
                    } else if (document.getElementById("uname").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your username.</b></font>';
                        document.getElementById("uname").focus();
                        return false;
                    } else if (document.getElementById("monthSelect1").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter date of birth.</b></font>';
                        return false;
                    } else
                        document.getElementById('f1').submit();

                } else
                {
                    if (document.getElementById("uname").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your username.</b></font>';
                        document.getElementById('pass_err_msg_email').innerHTML = '';
                        document.getElementById("uname").focus();
                        return false;
                    } else if (document.getElementById("pass_email").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your email.</b></font>';
                        document.getElementById('pass_err_msg_email').innerHTML = '';
                        document.getElementById("pass_stf_email").focus();
                        return false;
                    } else if (document.getElementById("pass_email").value == '0')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Incorrect login credential.</b></font>';
                        document.getElementById('pass_err_msg_email').innerHTML = '';
                        document.getElementById("pass_stf_email").focus();
                        return false;
                    } else
                        document.getElementById('f1').submit();
                }

            }
        } else
        {
            document.getElementById('divErr').innerHTML = '';
            document.getElementById('un_email').value = response[0];
            if (document.getElementById("valid_func").value == 'Y')
            {
                document.getElementById("valid_func").value = 'N';

                if (document.getElementById("uname_student").checked == true)
                {
                    if (document.getElementById("username_stn_id").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter student id.</b></font>';
                        document.getElementById("username_stn_id").focus();
                        return false;
                    } else if (document.getElementById("pass").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your password.</b></font>';
                        document.getElementById("pass").focus();
                        return false;
                    } else if (document.getElementById("monthSelect2").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your date of birth.</b></font>';
                        return false;
                    } else
                        document.getElementById('f1').submit();

                } else
                {
                    if (document.getElementById("pass").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your password.</b></font>';
                        document.getElementById('uname_err_msg_email').innerHTML = '';
                        document.getElementById("pass").focus();
                        return false;
                    } else if (document.getElementById("un_email").value == '')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Please enter your email.</b></font>';
                        document.getElementById('uname_err_msg_email').innerHTML = '';
                        document.getElementById("username_stf_email").focus();
                        return false;
                    } else if (document.getElementById("un_email").value == '0')
                    {
                        document.getElementById('divErr').innerHTML = '<font style="color:red"><b>Incorrect login credential.</b></font>';
                        document.getElementById('uname_err_msg_email').innerHTML = '';
                        document.getElementById("username_stf_email").focus();
                        return false;
                    } else
                        document.getElementById('f1').submit();
                }
            }
        }
    }

}

function forgotpassemail_error(err) {
    alert("Error: " + err);
}

function forgotpassvalidate_password(password, usrid, prof_id)
{

    var url = "PasswordCheck.php?password=" + password + "&usrid=" + usrid + "&prof_id=" + prof_id;
    ajax_call(url, forgotpassvalidate_callback, forgotpassvalidate_error);
}

function forgotpassvalidate_callback(data)
{
    var obj = document.getElementById('passwordStrength');

    if (data == '1')
    {
        obj.style.color = '#ff0000';
        obj.style.backgroundColor = "#cccccc";
        obj.innerHTML = 'Invalid password';
    }


}
function forgotpassvalidate_error(err)
{
    alert("Error: " + err);
}
function usercheck_init_noacess(i) {
    var obj = document.getElementById('ajax_output');
    obj.innerHTML = '';

    if (i.value.length < 1)
        return;

    var err = new Array();
    if (i.value.match(/[^A-Za-z0-9_@.]/))
        err[err.length] = 'Username can only contain letters, numbers, underscores, at the rate and dots';
    if (i.value.length < 3)
        err[err.length] = 'Username Too Short';
    if (err != '') {
        obj.style.color = '#ff0000';
        obj.innerHTML = err.join('<br />');
        return;
    }

    var pqr = i.value;
    if (i.value.length >= 3)
        ajax_call('Validator.php?u=' + i.value + 'user', usercheck_callback_noacess, usercheck_error);
}

function usercheck_callback_noacess(data) {
    var obj = document.getElementById('ajax_output');
    if (data == 1)
        obj.style.color = '#008800';
    else
        obj.style.color = '#ff0000';
    if (data == 1)
        obj.innerHTML = 'Username OK';
    else
    {
        obj.innerHTML = 'Username already taken';
        document.getElementById('FRESH_USERNAME').value = '';
        document.getElementById('FRESH_PASSWORD').value = '';
    }
}
function checkDuplicateName(param1, param2, param3)
{
    if (param1 != '')
    {
        var table_name = document.getElementById('checkDuplicateNameTable' + param1).value;
        var field_name = document.getElementById('checkDuplicateNameField' + param1).value;
        var msg = document.getElementById('checkDuplicateNameMsg' + param1).value;
    }

    if (table_name != '' && field_name != '' && msg != '')
    {
        var val = param2.value;
        var field_id = param2.id;
        ajax_call('CheckDuplicateName.php?table_name=' + table_name + '&field_name=' + field_name + '&val=' + val + '&field_id=' + field_id + '&msg=' + msg + '&id=' + param3, checkDuplicateNameCallback, checkDuplicateNameError);
    }
}
function checkDuplicateNameCallback(data)
{
    var data_return = data.split('_');
    var obj = document.getElementById('divErr');
    if (data_return[0] != 0)
    {
        obj.style.color = '#ff0000';
        obj.innerHTML = 'Duplicate ' + data_return[2];
        document.getElementById(data_return[1]).value = '';
    }
}
function checkDuplicateNameError(err)
{
    alert("Error: " + err);
}

function update_file_download()
{
    alert('ok');
    ajax_call('CheckVersion.php', checkVersion, checkVersionError);

}
function checkVersion(data)
{

    alert(data);
}
function chooseCpModal(id, table)
{

    ajax_call('ChooseCPModal.php?id=' + id + '&table_name=' + table, chooseCpModalCallback, chooseCpModalError);
}

function chooseCpModalCallback(data)
{
    var tdata = data.split('||');
    var obj = document.getElementById(tdata[0].trim());
    obj.innerHTML = tdata[1];
}
function chooseCpModalError(err)
{
    alert("Error: " + err);
}

function parentLookup(address_id)
{
    var ADDR_PRIM_L1        =   document.getElementById('values[student_address][HOME][STREET_ADDRESS_1]');
    if(ADDR_PRIM_L1)
    {
        ADDR_PRIM_L1        =   ADDR_PRIM_L1.value;
    }
    else
    {
        ADDR_PRIM_L1        =   "";
    }

    var ADDR_PRIM_L2        =   document.getElementById('values[student_address][HOME][STREET_ADDRESS_2]');
    if(ADDR_PRIM_L2)
    {
        ADDR_PRIM_L2        =   ADDR_PRIM_L2.value;
    }
    else
    {
        ADDR_PRIM_L2        =   "";
    }

    var ADDR_PRIM_CITY      =   document.getElementById('values[student_address][HOME][CITY]');
    if(ADDR_PRIM_CITY)
    {
        ADDR_PRIM_CITY      =   ADDR_PRIM_CITY.value;
    }
    else
    {
        ADDR_PRIM_CITY      =   "";
    }

    var ADDR_PRIM_STATE     =   document.getElementById('values[student_address][HOME][STATE]');
    if(ADDR_PRIM_STATE)
    {
        ADDR_PRIM_STATE     =   ADDR_PRIM_STATE.value;
    }
    else
    {
        ADDR_PRIM_STATE     =   "";
    }

    var ADDR_PRIM_ZIP       =   document.getElementById('values[student_address][HOME][ZIPCODE]');
    if(ADDR_PRIM_ZIP)
    {
        ADDR_PRIM_ZIP       =   ADDR_PRIM_ZIP.value;
    }
    else
    {
        ADDR_PRIM_ZIP       =   "";
    }

    var ADDR_PRIM_BUSNO     =   document.getElementById('values[student_address][HOME][BUS_NO]');
    if(ADDR_PRIM_BUSNO)
    {
        ADDR_PRIM_BUSNO     =   ADDR_PRIM_BUSNO.value;
    }
    else
    {
        ADDR_PRIM_BUSNO     =   "";
    }

    var ADDR_SAME_HOME      =   document.getElementById('r4');

    if(ADDR_SAME_HOME)
    {
        if(ADDR_SAME_HOME.getAttribute("checked") == "checked")
        {
            ADDR_SAME_HOME      =   "Y";
        }
        else
        {
            ADDR_SAME_HOME      =   "N";
        }
    }
    else
    {
        ADDR_SAME_HOME      =   "";
    }

    // alert(ADDR_SAME_HOME);

    if($('input[name="values[student_address][HOME][BUS_PICKUP]"]:checked').length > 0)
    {
        var ADDR_PRIM_BPU   =   "Y";
    }
    else
    {
        var ADDR_PRIM_BPU   =   "N";
    }

    if($('input[name="values[student_address][HOME][BUS_DROPOFF]"]:checked').length > 0)
    {
        var ADDR_PRIM_BDO   =   "Y";
    }
    else
    {
        var ADDR_PRIM_BDO   =   "N";
    }


    var ADDR_MAIL_L1        =   document.getElementById('values[student_address][MAIL][STREET_ADDRESS_1]');
    if(ADDR_MAIL_L1)
    {
        ADDR_MAIL_L1        =   ADDR_MAIL_L1.value;
    }
    else
    {
        ADDR_MAIL_L1        =   "";
    }

    var ADDR_MAIL_L2        =   document.getElementById('values[student_address][MAIL][STREET_ADDRESS_2]');
    if(ADDR_MAIL_L2)
    {
        ADDR_MAIL_L2        =   ADDR_MAIL_L2.value;
    }
    else
    {
        ADDR_MAIL_L2        =   "";
    }

    var ADDR_MAIL_CITY      =   document.getElementById('values[student_address][MAIL][CITY]');
    if(ADDR_MAIL_CITY)
    {
        ADDR_MAIL_CITY      =   ADDR_MAIL_CITY.value;
    }
    else
    {
        ADDR_MAIL_CITY      =   "";
    }

    var ADDR_MAIL_STATE     =   document.getElementById('values[student_address][MAIL][STATE]');
    if(ADDR_MAIL_STATE)
    {
        ADDR_MAIL_STATE     =   ADDR_MAIL_STATE.value;
    }
    else
    {
        ADDR_MAIL_STATE     =   "";
    }

    var ADDR_MAIL_ZIP       =   document.getElementById('values[student_address][MAIL][ZIPCODE]');
    if(ADDR_MAIL_ZIP)
    {
        ADDR_MAIL_ZIP       =   ADDR_MAIL_ZIP.value;
    }
    else
    {
        ADDR_MAIL_ZIP       =   "";
    }

    if($('#same_addr').is(':checked') || ADDR_SAME_HOME == 'Y')
    {
        var ADDR_SAME_AS    =   "Y";

        // ADDR_MAIL_L1        =   ADDR_PRIM_L1;
        // ADDR_MAIL_L2        =   ADDR_PRIM_L2;
        // ADDR_MAIL_CITY      =   ADDR_PRIM_CITY;
        // ADDR_MAIL_STATE     =   ADDR_PRIM_STATE;
        // ADDR_MAIL_ZIP       =   ADDR_PRIM_ZIP;
    }
    else
    {
        var ADDR_SAME_AS    =   "N";
    }


    // FOR PRIMARY CONTACTS
    var ADDR_CONT_RSHIP     =   document.getElementById('values[people][PRIMARY][RELATIONSHIP]');
    var ADDR_CONT_RSHIP_X   =   document.getElementById('inputvalues[people][PRIMARY][RELATIONSHIP]');
    if(ADDR_CONT_RSHIP)
    {
        ADDR_CONT_RSHIP     =   ADDR_CONT_RSHIP.value;
    }
    else if(ADDR_CONT_RSHIP_X)
    {
        ADDR_CONT_RSHIP     =   ADDR_CONT_RSHIP_X.value;
    }
    else
    {
        ADDR_CONT_RSHIP     =   "";
    }

    var ADDR_CONT_FIRST     =   document.getElementById('values[people][PRIMARY][FIRST_NAME]');
    var ADDR_CONT_FIRST_X   =   document.getElementById('inputvalues[people][PRIMARY][FIRST_NAME]');
    if(ADDR_CONT_FIRST)
    {
        ADDR_CONT_FIRST     =   ADDR_CONT_FIRST.value;
    }
    else if(ADDR_CONT_FIRST_X)
    {
        ADDR_CONT_FIRST     =   ADDR_CONT_FIRST_X.value;
    }
    else
    {
        ADDR_CONT_FIRST     =   "";
    }

    var ADDR_CONT_LAST      =   document.getElementById('values[people][PRIMARY][LAST_NAME]');
    var ADDR_CONT_LAST_X    =   document.getElementById('inputvalues[people][PRIMARY][LAST_NAME]');
    if(ADDR_CONT_LAST)
    {
        ADDR_CONT_LAST      =   ADDR_CONT_LAST.value;
    }
    else if(ADDR_CONT_LAST_X)
    {
        ADDR_CONT_LAST      =   ADDR_CONT_LAST_X.value;
    }
    else
    {
        ADDR_CONT_LAST      =   "";
    }

    var ADDR_CONT_HOME      =   document.getElementById('values[people][PRIMARY][HOME_PHONE]');
    var ADDR_CONT_HOME_X    =   document.getElementById('inputvalues[people][PRIMARY][HOME_PHONE]');
    if(ADDR_CONT_HOME)
    {
        ADDR_CONT_HOME      =   ADDR_CONT_HOME.value;
    }
    else if(ADDR_CONT_HOME_X)
    {
        ADDR_CONT_HOME      =   ADDR_CONT_HOME_X.value;
    }
    else
    {
        ADDR_CONT_HOME      =   "";
    }

    var ADDR_CONT_WORK      =   document.getElementById('values[people][PRIMARY][WORK_PHONE]');
    var ADDR_CONT_WORK_X    =   document.getElementById('inputvalues[people][PRIMARY][WORK_PHONE]');
    if(ADDR_CONT_WORK)
    {
        ADDR_CONT_WORK      =   ADDR_CONT_WORK.value;
    }
    else if(ADDR_CONT_WORK_X)
    {
        ADDR_CONT_WORK      =   ADDR_CONT_WORK_X.value;
    }
    else
    {
        ADDR_CONT_WORK      =   "";
    }

    var ADDR_CONT_CELL      =   document.getElementById('values[people][PRIMARY][CELL_PHONE]');
    var ADDR_CONT_CELL_X    =   document.getElementById('inputvalues[people][PRIMARY][CELL_PHONE]');
    if(ADDR_CONT_CELL)
    {
        ADDR_CONT_CELL      =   ADDR_CONT_CELL.value;
    }
    else if(ADDR_CONT_CELL_X)
    {
        ADDR_CONT_CELL      =   ADDR_CONT_CELL_X.value;
    }
    else
    {
        ADDR_CONT_CELL      =   "";
    }

    var ADDR_CONT_MAIL      =   document.getElementById('values[people][PRIMARY][EMAIL]');
    var ADDR_CONT_MAIL_X    =   document.getElementById('inputvalues[people][PRIMARY][EMAIL]');
    if(ADDR_CONT_MAIL)
    {
        ADDR_CONT_MAIL      =   ADDR_CONT_MAIL.value;
    }
    else if(ADDR_CONT_MAIL_X)
    {
        ADDR_CONT_MAIL      =   ADDR_CONT_MAIL_X.value;
    }
    else
    {
        ADDR_CONT_MAIL      =   "";
    }

    var ADDR_CONT_CUSTODY   =   document.getElementById('values[people][PRIMARY][CUSTODY]');
    if(ADDR_CONT_CUSTODY)
    {
        // if($("#values[people][PRIMARY][CUSTODY]").is(':checked'))
        if(ADDR_CONT_CUSTODY.value == "Y")
        {
            ADDR_CONT_CUSTODY   =   "Y";
        }
        else
        {
            ADDR_CONT_CUSTODY   =   "N";
        }
    }
    else
    {
        ADDR_CONT_CUSTODY   =   "N";
    }

    var ADDR_CONT_PORTAL    =   document.getElementById('portal_1');
    if(ADDR_CONT_PORTAL)
    {
        if(ADDR_CONT_PORTAL.checked == true)
        {
            ADDR_CONT_PORTAL    =   "Y";
        }
        else
        {
            ADDR_CONT_PORTAL    =   "N";
        }
    }
    else
    {
        ADDR_CONT_PORTAL    =   "";
    }

    var ADDR_CONT_USRN      =   document.getElementById('values[people][PRIMARY][USER_NAME]');
    var ADDR_CONT_USRN_X    =   document.getElementById('inputvalues[people][PRIMARY][USER_NAME]');
    if(ADDR_CONT_USRN)
    {
        ADDR_CONT_USRN      =   ADDR_CONT_USRN.value;
    }
    else if(ADDR_CONT_USRN_X)
    {
        ADDR_CONT_USRN      =   ADDR_CONT_USRN_X.value;
    }
    else
    {
        ADDR_CONT_USRN      =   "";
    }

    var ADDR_CONT_PSWD      =   document.getElementById('values[people][PRIMARY][PASSWORD]');
    var ADDR_CONT_PSWD_X      =   document.getElementById('inputvalues[people][PRIMARY][PASSWORD]');
    if(ADDR_CONT_PSWD)
    {
        ADDR_CONT_PSWD      =   ADDR_CONT_PSWD.value;
    }
    else if(ADDR_CONT_PSWD_X)
    {
        ADDR_CONT_PSWD      =   ADDR_CONT_PSWD_X.value;  
    }
    else
    {
        ADDR_CONT_PSWD      =   "";
    }

    if($('#rps').is(':checked'))
    {
        var ADDR_CONT_SAHA  =   "Y";
    }
    else
    {
        var ADDR_CONT_SAHA  =   "N";
    }

    if($('#rpn').is(':checked'))
    {
        var ADDR_CONT_ADNA  =   "Y";
    }
    else
    {
        var ADDR_CONT_ADNA  =   "N";
    }

    var ADDR_CONT_LIN1      =   document.getElementById('values[student_address][PRIMARY][STREET_ADDRESS_1]');
    var ADDR_CONT_LIN1_X    =   document.getElementById('inputvalues[student_address][PRIMARY][STREET_ADDRESS_1]');
    if(ADDR_CONT_LIN1)
    {
        ADDR_CONT_LIN1      =   ADDR_CONT_LIN1.value;
    }
    else if(ADDR_CONT_LIN1_X)
    {
        ADDR_CONT_LIN1      =   ADDR_CONT_LIN1_X.value;
    }
    else
    {
        ADDR_CONT_LIN1      =   "";
    }

    var ADDR_CONT_LIN2      =   document.getElementById('values[student_address][PRIMARY][STREET_ADDRESS_2]');
    var ADDR_CONT_LIN2_X    =   document.getElementById('inputvalues[student_address][PRIMARY][STREET_ADDRESS_2]');
    if(ADDR_CONT_LIN2)
    {
        ADDR_CONT_LIN2      =   ADDR_CONT_LIN2.value;
    }
    else if(ADDR_CONT_LIN2_X)
    {
        ADDR_CONT_LIN2      =   ADDR_CONT_LIN2_X.value;
    }
    else
    {
        ADDR_CONT_LIN2      =   "";
    }

    var ADDR_CONT_CITY      =   document.getElementById('values[student_address][PRIMARY][CITY]');
    var ADDR_CONT_CITY_X    =   document.getElementById('inputvalues[student_address][PRIMARY][CITY]');
    if(ADDR_CONT_CITY)
    {
        ADDR_CONT_CITY      =   ADDR_CONT_CITY.value;
    }
    else if(ADDR_CONT_CITY_X)
    {
        ADDR_CONT_CITY      =   ADDR_CONT_CITY_X.value;
    }
    else
    {
        ADDR_CONT_CITY      =   "";
    }

    var ADDR_CONT_STAT      =   document.getElementById('values[student_address][PRIMARY][STATE]');
    var ADDR_CONT_STAT_X    =   document.getElementById('inputvalues[student_address][PRIMARY][STATE]');
    if(ADDR_CONT_STAT)
    {
        ADDR_CONT_STAT      =   ADDR_CONT_STAT.value;
    }
    else if(ADDR_CONT_STAT_X)
    {
        ADDR_CONT_STAT      =   ADDR_CONT_STAT_X.value;
    }
    else
    {
        ADDR_CONT_STAT      =   "";
    }

    var ADDR_CONT_ZIP       =   document.getElementById('values[student_address][PRIMARY][ZIPCODE]');
    var ADDR_CONT_ZIP_X     =   document.getElementById('inputvalues[student_address][PRIMARY][ZIPCODE]');
    if(ADDR_CONT_ZIP)
    {
        ADDR_CONT_ZIP       =   ADDR_CONT_ZIP.value;
    }
    else if(ADDR_CONT_ZIP_X)
    {
        ADDR_CONT_ZIP       =   ADDR_CONT_ZIP_X.value;
    }
    else
    {
        ADDR_CONT_ZIP       =   "";
    }

    var CHK_HOME_ADDR_PRIM  =   document.getElementById("uniform-prim_addr");
    if(CHK_HOME_ADDR_PRIM)
    {
        var F_E_CHK_HAP         =   CHK_HOME_ADDR_PRIM.getElementsByTagName("span");
        if(F_E_CHK_HAP[0].className == "checked")
        {
            CHK_HOME_ADDR_PRIM  =   "Y";
        }
        else
        {
            CHK_HOME_ADDR_PRIM  =   "N";
        }
    }
if (document.getElementById("hidden_primary")){
    var SELECTED_PRIMARY    =   document.getElementById("hidden_primary").value;
}


    // FOR SECONDARY CONTACTS
    var SECN_CONT_RSHIP     =   document.getElementById('values[people][SECONDARY][RELATIONSHIP]');
    var SECN_CONT_RSHIP_X   =   document.getElementById('inputvalues[people][SECONDARY][RELATIONSHIP]');
    if(SECN_CONT_RSHIP)
    {
        SECN_CONT_RSHIP     =   SECN_CONT_RSHIP.value;
    }
    else if(SECN_CONT_RSHIP_X)
    {
        SECN_CONT_RSHIP     =   SECN_CONT_RSHIP_X.value;
    }
    else
    {
        SECN_CONT_RSHIP     =   "";
    }

    var SECN_CONT_FIRST     =   document.getElementById('values[people][SECONDARY][FIRST_NAME]');
    var SECN_CONT_FIRST_X   =   document.getElementById('inputvalues[people][SECONDARY][FIRST_NAME]');
    if(SECN_CONT_FIRST)
    {
        SECN_CONT_FIRST     =   SECN_CONT_FIRST.value;
    }
    else if(SECN_CONT_FIRST_X)
    {
        SECN_CONT_FIRST     =   SECN_CONT_FIRST_X.value;
    }
    else
    {
        SECN_CONT_FIRST     =   "";
    }

    var SECN_CONT_LAST      =   document.getElementById('values[people][SECONDARY][LAST_NAME]');
    var SECN_CONT_LAST_X    =   document.getElementById('inputvalues[people][SECONDARY][LAST_NAME]');
    if(SECN_CONT_LAST)
    {
        SECN_CONT_LAST      =   SECN_CONT_LAST.value;
    }
    else if(SECN_CONT_LAST_X)
    {
        SECN_CONT_LAST      =   SECN_CONT_LAST_X.value;
    }
    else
    {
        SECN_CONT_LAST      =   "";
    }

    var SECN_CONT_HOME      =   document.getElementById('values[people][SECONDARY][HOME_PHONE]');
    var SECN_CONT_HOME_X    =   document.getElementById('inputvalues[people][SECONDARY][HOME_PHONE]');
    if(SECN_CONT_HOME)
    {
        SECN_CONT_HOME      =   SECN_CONT_HOME.value;
    }
    else if(SECN_CONT_HOME_X)
    {
        SECN_CONT_HOME      =   SECN_CONT_HOME_X.value;
    }
    else
    {
        SECN_CONT_HOME      =   "";
    }

    var SECN_CONT_WORK      =   document.getElementById('values[people][SECONDARY][WORK_PHONE]');
    var SECN_CONT_WORK_X    =   document.getElementById('inputvalues[people][SECONDARY][WORK_PHONE]');
    if(SECN_CONT_WORK)
    {
        SECN_CONT_WORK      =   SECN_CONT_WORK.value;
    }
    else if(SECN_CONT_WORK_X)
    {
        SECN_CONT_WORK      =   SECN_CONT_WORK_X.value;
    }
    else
    {
        SECN_CONT_WORK      =   "";
    }

    var SECN_CONT_CELL      =   document.getElementById('values[people][SECONDARY][CELL_PHONE]');
    var SECN_CONT_CELL_X    =   document.getElementById('inputvalues[people][SECONDARY][CELL_PHONE]');
    if(SECN_CONT_CELL)
    {
        SECN_CONT_CELL      =   SECN_CONT_CELL.value;
    }
    else if(SECN_CONT_CELL_X)
    {
        SECN_CONT_CELL      =   SECN_CONT_CELL_X.value;
    }
    else
    {
        SECN_CONT_CELL      =   "";
    }

    var SECN_CONT_MAIL      =   document.getElementById('values[people][SECONDARY][EMAIL]');
    var SECN_CONT_MAIL_X    =   document.getElementById('inputvalues[people][SECONDARY][EMAIL]');
    if(SECN_CONT_MAIL)
    {
        SECN_CONT_MAIL      =   SECN_CONT_MAIL.value;
    }
    else if(SECN_CONT_MAIL_X)
    {
        SECN_CONT_MAIL      =   SECN_CONT_MAIL_X.value;
    }
    else
    {
        SECN_CONT_MAIL      =   "";
    }

    var SECN_CONT_CUSTODY   =   document.getElementById('values[people][SECONDARY][CUSTODY]');
    if(SECN_CONT_CUSTODY)
    {
        // if($("#values[people][SECONDARY][CUSTODY]").is(':checked'))
        if(SECN_CONT_CUSTODY.value == "Y")
        {
            SECN_CONT_CUSTODY   =   "Y";
        }
        else
        {
            SECN_CONT_CUSTODY   =   "N";
        }
    }
    else
    {
        SECN_CONT_CUSTODY   =   "N";
    }

    var SECN_CONT_PORTAL    =   document.getElementById('portal_2');
    if(SECN_CONT_PORTAL)
    {
        if(SECN_CONT_PORTAL.checked == true)
        {
            SECN_CONT_PORTAL    =   "Y";
        }
        else
        {
            SECN_CONT_PORTAL    =   "N";
        }
    }
    else
    {
        SECN_CONT_PORTAL    =   "";
    }

    var SECN_CONT_USRN      =   document.getElementById('values[people][SECONDARY][USER_NAME]');
    var SECN_CONT_USRN_X    =   document.getElementById('inputvalues[people][SECONDARY][USER_NAME]');
    if(SECN_CONT_USRN)
    {
        SECN_CONT_USRN      =   SECN_CONT_USRN.value;
    }
    else if(SECN_CONT_USRN_X)
    {
        SECN_CONT_USRN      =   SECN_CONT_USRN_X.value;
    }
    else
    {
        SECN_CONT_USRN      =   "";
    }

    var SECN_CONT_PSWD      =   document.getElementById('values[people][SECONDARY][PASSWORD]');
    var SECN_CONT_PSWD_X    =   document.getElementById('inputvalues[people][SECONDARY][PASSWORD]');
    if(SECN_CONT_PSWD)
    {
        SECN_CONT_PSWD      =   SECN_CONT_PSWD.value;
    }
    else if(SECN_CONT_PSWD_X)
    {
        SECN_CONT_PSWD      =   SECN_CONT_PSWD_X.value;
    }
    else
    {
        SECN_CONT_PSWD      =   "";
    }

    var SECN_CONT_LIN1      =   document.getElementById('values[student_address][SECONDARY][STREET_ADDRESS_1]');
    var SECN_CONT_LIN1_X    =   document.getElementById('inputvalues[student_address][SECONDARY][STREET_ADDRESS_1]');
    if(SECN_CONT_LIN1)
    {
        SECN_CONT_LIN1      =   SECN_CONT_LIN1.value;
    }
    else if(SECN_CONT_LIN1_X)
    {
        SECN_CONT_LIN1      =   SECN_CONT_LIN1_X.value;
    }
    else
    {
        SECN_CONT_LIN1      =   "";
    }

    var SECN_CONT_LIN2      =   document.getElementById('values[student_address][SECONDARY][STREET_ADDRESS_2]');
    var SECN_CONT_LIN2_X    =   document.getElementById('inputvalues[student_address][SECONDARY][STREET_ADDRESS_2]');
    if(SECN_CONT_LIN2)
    {
        SECN_CONT_LIN2      =   SECN_CONT_LIN2.value;
    }
    else if(SECN_CONT_LIN2_X)
    {
        SECN_CONT_LIN2      =   SECN_CONT_LIN2_X.value;
    }
    else
    {
        SECN_CONT_LIN2      =   "";
    }

    var SECN_CONT_CITY      =   document.getElementById('values[student_address][SECONDARY][CITY]');
    var SECN_CONT_CITY_X    =   document.getElementById('inputvalues[student_address][SECONDARY][CITY]');
    if(SECN_CONT_CITY)
    {
        SECN_CONT_CITY      =   SECN_CONT_CITY.value;
    }
    else if(SECN_CONT_CITY_X)
    {
        SECN_CONT_CITY      =   SECN_CONT_CITY_X.value;
    }
    else
    {
        SECN_CONT_CITY      =   "";
    }

    var SECN_CONT_STAT      =   document.getElementById('values[student_address][SECONDARY][STATE]');
    var SECN_CONT_STAT_X    =   document.getElementById('inputvalues[student_address][SECONDARY][STATE]');
    if(SECN_CONT_STAT)
    {
        SECN_CONT_STAT      =   SECN_CONT_STAT.value;
    }
    else if(SECN_CONT_STAT_X)
    {
        SECN_CONT_STAT      =   SECN_CONT_STAT_X.value;
    }
    else
    {
        SECN_CONT_STAT      =   "";
    }

    var SECN_CONT_ZIP       =   document.getElementById('values[student_address][SECONDARY][ZIPCODE]');
    var SECN_CONT_ZIP_X     =   document.getElementById('inputvalues[student_address][SECONDARY][ZIPCODE]');
    if(SECN_CONT_ZIP)
    {
        SECN_CONT_ZIP       =   SECN_CONT_ZIP.value;
    }
    else if(SECN_CONT_ZIP_X)
    {
        SECN_CONT_ZIP       =   SECN_CONT_ZIP_X.value;
    }
    else
    {
        SECN_CONT_ZIP       =   "";
    }

    var CHK_HOME_ADDR_SECN  =   document.getElementById("uniform-sec_addr");
    if(CHK_HOME_ADDR_SECN)
    {
        var F_E_CHK_HAS         =   CHK_HOME_ADDR_SECN.getElementsByTagName("span");
        if(F_E_CHK_HAS[0].className == "checked")
        {
            CHK_HOME_ADDR_SECN  =   "Y";
        }
        else
        {
            CHK_HOME_ADDR_SECN  =   "N";
        }
    }
if(document.getElementById("hidden_secondary")){
    var SELECTED_SECONDARY  =   document.getElementById("hidden_secondary").value;
    }

    var USERINFO_FIRST_NAME =   document.getElementById('USERINFO_FIRST_NAME').value;
    var USERINFO_LAST_NAME  =   document.getElementById('USERINFO_LAST_NAME').value;
    var USERINFO_EMAIL      =   document.getElementById('USERINFO_EMAIL').value;
    var USERINFO_MOBILE     =   document.getElementById('USERINFO_MOBILE').value;
    var USERINFO_SADD       =   document.getElementById('USERINFO_SADD').value;
    var USERINFO_CITY       =   document.getElementById('USERINFO_CITY').value;
    var USERINFO_STATE      =   document.getElementById('USERINFO_STATE').value;
    var USERINFO_ZIP        =   document.getElementById('USERINFO_ZIP').value;
    var p_type              =   document.getElementById('p_type').value;
    var other_p_erson_id    =   document.getElementById('other_p_erson_id').value;

    // alert(ADDR_PRIM_BPU);
    
    $.ajax({
        url     :   "HoldAddressFields.php",
        type    :   "post",
        data    :   { ADDR_PRIM_L1, ADDR_PRIM_L2, ADDR_PRIM_CITY, ADDR_PRIM_STATE, ADDR_PRIM_ZIP, ADDR_PRIM_BUSNO, ADDR_SAME_HOME, ADDR_PRIM_BPU, ADDR_PRIM_BDO, ADDR_SAME_AS, ADDR_MAIL_L1, ADDR_MAIL_L2, ADDR_MAIL_CITY, ADDR_MAIL_STATE, ADDR_MAIL_ZIP, ADDR_CONT_RSHIP, ADDR_CONT_FIRST, ADDR_CONT_LAST, ADDR_CONT_HOME, ADDR_CONT_WORK, ADDR_CONT_CELL, ADDR_CONT_MAIL, ADDR_CONT_CUSTODY, ADDR_CONT_PORTAL, ADDR_CONT_USRN, ADDR_CONT_PSWD, ADDR_CONT_SAHA, ADDR_CONT_ADNA, ADDR_CONT_LIN1, ADDR_CONT_LIN2, ADDR_CONT_CITY, ADDR_CONT_STAT, ADDR_CONT_ZIP, CHK_HOME_ADDR_PRIM, SECN_CONT_RSHIP, SECN_CONT_FIRST, SECN_CONT_LAST, SECN_CONT_HOME, SECN_CONT_WORK, SECN_CONT_CELL, SECN_CONT_MAIL, SECN_CONT_CUSTODY, SECN_CONT_PORTAL, SECN_CONT_USRN, SECN_CONT_PSWD, SECN_CONT_LIN1, SECN_CONT_LIN2, SECN_CONT_CITY, SECN_CONT_STAT, SECN_CONT_ZIP, CHK_HOME_ADDR_SECN, SELECTED_PRIMARY, SELECTED_SECONDARY },
        success :   function(sculz)
        {
            console.log("ADDRESS STORED IN SESSION");

            // alert(sculz);
        }
    })

    ajax_call('ParentLookup.php?USERINFO_FIRST_NAME=' + USERINFO_FIRST_NAME + '&USERINFO_LAST_NAME=' + USERINFO_LAST_NAME + '&USERINFO_EMAIL=' + USERINFO_EMAIL + '&USERINFO_MOBILE=' + USERINFO_MOBILE + '&USERINFO_SADD=' + USERINFO_SADD + '&USERINFO_CITY=' + USERINFO_CITY + '&USERINFO_STATE=' + USERINFO_STATE + '&USERINFO_ZIP=' + USERINFO_ZIP + '&address_id=' + address_id + '&p_type=' + p_type + '&other_p_erson_id=' + other_p_erson_id, parentLookupCallback, chooseCpModalError);
}

function modal_parenttype(type, other_p_erson_id = '')
{

    $('#modal_default_lookup').modal('show');
    $("#other_p_erson_id").val(other_p_erson_id);

    $("#p_type").val(type);
}

function parentLookupCallback(data)
{

//    var tdata=data.split('||');
//    var obj = document.getElementById(tdata[0].trim());
//    obj.innerHTML = tdata[1];

    document.getElementById("parent_res").innerHTML = data;
}

function SelectedParent(address_id, type, other_p_erson_id = '')
{
    var selected_staff = document.querySelector('input[name="staff"]:checked').value;
    //ajax_call('modules/students/.php?id=' + id+'&table_name='+table, SelectedParentCallback, chooseCpModalError);

    // if(type == 'primary')
    // {
    //     var typex   =   "PRIMARY";
    // }
    // if(type == 'secondary')
    // {
    //     var typex   =   "SECONDARY";
    // }

    $("#modal_default_lookup").modal('toggle');
    
    if (type == 'other')
        window.location.href = 'Modules.php?modname=students/Student.php&include=AddressInc&category_id=3&func=search_select&type=' + type + '&nfunc=status&ajax=true&button=Select&con_info=old&add_id=' + other_p_erson_id + '&address_id=' + address_id + '&staff=' + selected_staff + '&person_id=' + other_p_erson_id;
    else
        window.location.href = 'Modules.php?modname=students/Student.php&include=AddressInc&category_id=3&func=search_select&type=' + type + '&nfunc=status&ajax=true&button=Select&add_id=&address_id=' + address_id + '&staff=' + selected_staff;

    // var detailsOfSelectedContact    =   selectedContact(selected_staff);

    // if(type == 'primary' || type == "secondary")
    // {
    //     $.ajax({
    //         url     :   "KnowContactDetails.php",
    //         type    :   "post",
    //         data    :   { selected_staff },
    //         success :   function(reponz)
    //         {
    //             // alert(reponz);
    //             var recallContact   =   JSON.parse(reponz);

    //             // alert(recallContact.FIRST_NAME);

    //             // $("#values[people][PRIMARY][RELATIONSHIP]").val();

    //             $("#values[people]["+typex+"][FIRST_NAME]").val(recallContact.FIRST_NAME);
    //             $("#values[people]["+typex+"][LAST_NAME]").val(recallContact.LAST_NAME);
    //             $("#values[people]["+typex+"][HOME_PHONE]").val(recallContact.HOME_PHONE);
    //             $("#values[people]["+typex+"][WORK_PHONE]").val(recallContact.WORK_PHONE);
    //             $("#values[people]["+typex+"][CELL_PHONE]").val(recallContact.CELL_PHONE);
    //             $("#values[people]["+typex+"][EMAIL]").val(recallContact.EMAIL);
    //         }
    //     })
    // }
    // else
    // {
    //     window.location.href = 'Modules.php?modname=students/Student.php&include=AddressInc&category_id=3&func=search_select&type=' + type + '&nfunc=status&ajax=true&button=Select&con_info=old&add_id=' + other_p_erson_id + '&address_id=' + address_id + '&staff=' + selected_staff + '&person_id=' + other_p_erson_id;
    // }
}

// function selectedContact(selected_staff)
// {
//     $.ajax({
//         url     :   "KnowContactDetails.php",
//         type    :   "post",
//         data    :   { selected_staff },

//     })
// }

function cpActionModal(title, subject_id, course_id, course_period_id)
{
    ajax_call('CpSessionSet.php?title=' + title + '&subject_id=' + subject_id + '&course_id=' + course_id + '&course_period_id=' + course_period_id, cpActionModalCallback);
}

function cpActionModalCallback(data)
{
    document.getElementById('course_div').innerHTML = data;
    $('#modal_default').modal('hide');
    $('.modal-backdrop').remove();
}

function chooseCpModalSearch(id, table)
{
    ajax_call('ChooseCpSearch.php?id=' + id + '&table_name=' + table, chooseCpModalSearchCallback, chooseCpModalSearchError);
}

function chooseCpModalSearchCallback(data)
{
    var tdata = data.split('||');
    var obj = document.getElementById(tdata[0].trim());
    obj.innerHTML = tdata[1];
}
function chooseCpModalSearchError(err)
{
    alert("Error: " + err);
}

function chooseCpModalSearchRequest(id, table)
{
    ajax_call('ChooseRequestSearch.php?id=' + id + '&table_name=' + table, chooseCpModalSearchRequestCallback, chooseCpModalSearchRequestError);
}

function chooseCpModalSearchRequestCallback(data)
{

    var tdata = data.split('||');
    var obj = document.getElementById(tdata[0].trim());
    obj.innerHTML = tdata[1];
}
function chooseCpModalSearchRequestError(err)
{
    alert("Error: " + err);
}

function MassDropModal(id, table)
{
    ajax_call('MassDropModal.php?id=' + id + '&table_name=' + table, MassDropModalCallback, MassDropModalError);
}

function MassDropModalCallback(data)
{
    var tdata = data.split('||');
    var obj = document.getElementById(tdata[0].trim());
    obj.innerHTML = tdata[1];
}
function MassDropModalError(err)
{
    alert("Error: " + err);
}

function MassDropSessionSet(title, subject_id, course_id, course_period_id)
{
    ajax_call('MassDropSessionSet.php?title=' + title + '&subject_id=' + subject_id + '&course_id=' + course_id + '&course_period_id=' + course_period_id, MassDropSessionSetCallback);
}

function MassDropSessionSetCallback(data)
{
    document.getElementById('course_div').innerHTML = data;
    $('#modal_default').modal('hide');
    $('.modal-backdrop').remove();
}

function CalendarModal(event_id, cal_id, date, year, month, tochar)
{

    $('#modal_default_calendar').modal('show');
    ajax_call('CalendarModal.php?event_id=' + event_id + '&calendar_id=' + cal_id + '&school_date=' + date + '&month=' + month + '&year=' + year + '&tochar=tochar', CalendarModalCallback, chooseCpModalError);

}


function CalendarModalCallback(data)
{

//    var tdata=data.split('||');
//    var obj = document.getElementById(tdata[0].trim());
//    obj.innerHTML = tdata[1];

    document.getElementById("modal-res").innerHTML = data;
}


function CalendarModalAssignment(assignment_id)
{

    $('#modal_default_calendar').modal('show');
    ajax_call('CalendarModal.php?assignment_id=' + assignment_id, CalendarModalCallback, chooseCpModalError);
}


function BlockModalPeriod(subject_id, course_id, course_period_id, calendar_id, date, mode, id, add1)
{
    $('#modal_default_block_cp').modal('show');
    ajax_call('CoursePeriodModal.php?subject_id=' + subject_id + '&course_id=' + course_id + '&course_period_id=' + course_period_id + '&calendar_id=' + calendar_id + '&meet_date=' + date + '&mode=' + mode + '&id=' + id + '&add=' + add1, CalendarModalCallback, chooseCpModalError);


}

function MassScheduleModal(id, table)
{
    ajax_call('MassScheduleModal.php?id=' + id + '&table_name=' + table, MassScheduleModalCallback, MassScheduleModalError);
}

function MassScheduleModalCallback(data)
{
    var tdata = data.split('||');
    var obj = document.getElementById(tdata[0].trim());
    obj.innerHTML = tdata[1];
}
function MassScheduleModalError(err)
{
    alert("Error: " + err);
}

function MassScheduleSessionSet(title, subject_id, course_id, course_period_id)
{
    ajax_call('MassScheduleSessionSet.php?title=' + title + '&subject_id=' + subject_id + '&course_id=' + course_id + '&course_period_id=' + course_period_id, MassScheduleSessionSetCallback);
}

function MassScheduleSessionSetCallback(data)
{
//    alert(data);
    var tdata = data.split('|_*|*_|');
//    obj.innerHTML = tdata[1];
    document.getElementById('showTitle').innerHTML = tdata[2];
    document.getElementById('val_marking_period_id').value = tdata[1];
    document.getElementById('course_div').innerHTML = tdata[0];
    $('#modal_default').modal('hide');
    $('.modal-backdrop').remove();
}

function TransferredOutModal(modfunc, student_id, drop_code)
{
    ajax_call('TransferredOutModal.php?modfunc=' + modfunc + '&student_id=' + student_id + '&drop_code=' + drop_code, TransferredOutModalCallback, MassScheduleModalError);
}

function TransferredOutModalCallback(data) {
    $('#modal_default_transferred_out').modal('show');
    $("#modal-res").html(data);
}
function massScheduleCourseToAdd()
{
    document.getElementById('course_div').innerHTML='';
}

function getSchoolScheduleReport(this_INC)
{
    if(this_DATE != '')
    {
        var this_DATE = document.getElementById("week_date_"+this_INC).value;

        document.getElementById("take_date").value = this_DATE;

        document.getElementById("ssrfrm").submit();
    }

    console.log(this_DATE);
}

function loadFilterList()
{
    window.$("#stuf_loader").show();

    ajax_call("StudentFilterList.php", loadFilterListCallback, loadFilterListErrors);
}

function loadFilterListCallback(check_return)
{
    window.$("#stuf_loader").hide();

    //console.log(check_return);
    window.$("#view_resp").html(check_return);
}

function loadFilterListErrors(exceptions)
{
    window.$("#stuf_loader").hide();
    
    console.log(exceptions);
}

function loadDataTablePagination(pageCount)
{
    window.$("#loading-image").show();

    ajax_call("PrepareDataTable.php?loadpage="+pageCount, loadDataTablePaginationCallback, loadDataTablePaginationErrors);
}

function loadDataTablePaginationCallback(check_return)
{
    window.$("#loading-image").hide();

    window.$("#tabs_resp").html(check_return);
    window.$("#hidden_checkboxes > input").each(function() {
        let checkboxId = $(this).data('checkbox-hidden-id')
        window.$("[value= "+checkboxId + "]").each(function() {
            $(this).attr('checked', true)
        })
        window.$("[id= "+checkboxId + "]").each(function() {
            $(this).attr('checked', true)
        })
    })

    if(document.getElementById('checked_all') && document.getElementById('checked_all').value == 'true'){
        window.$('input[type=checkbox][name=controller]').attr('checked', true)
    }
}

function loadDataTablePaginationErrors(exceptions)
{
    window.$("#loading-image").hide();
    
    console.warn(exceptions);
}

function generateDataTableSpreadsheet()
{
    window.open("ExportDataTable.php", '_blank');
}
