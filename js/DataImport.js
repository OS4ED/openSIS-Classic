function drawmapping(selected_value, val_id, div_id)
{
    if (selected_value != '')
    {
        div_id.innerHTML = '<img src=modules/tools/assets/images/dashed_arrow_mapping.png />';
        val_id.value = selected_value;
    }
    else
    {
        div_id.innerHTML = '';
        val_id.value = '';
    }
}
//For Full Arrow
function drawmapping_full(selected_value, val_id, div_id)
{
    if (selected_value != '')
    {
        div_id.innerHTML = '<img src=modules/tools/assets/images/arrow_mapping.png />';
        val_id.value = selected_value;
    }
    else
    {
        div_id.innerHTML = '';
        val_id.value = '';
    }
}


function map_upload_validation()
{

    var data = document.getElementById('file_id').value;
    if (data != '')
    {
        return true;
    }
    else
    {
        return false;
    }



}
function valid_mapping_student(option, this_DET)
{
    var this_button_id = this_DET.id;

    var fn = 0;
    var ln = 0;
    var gn = 0;
    var pl = 0;
    var bd = 0;
    var un = 0;
    var es_gd = 0;
    var st_d = 0;

    //variable for 0 or 1 value
    var phone = 0;
    var city = 0;
    var address = 0;
    var prim_student_relation = 0;
    var pri_last_name = 0;
    var pri_first_name = 0;
    var sec_student_relation = 0;
    var sec_last_name = 0;
    var sec_first_name = 0;
    var grade_id = 0;
    var middle_name = 0;
    var state = 0;

    for (var i = 1; i <= option; i++)
    {
        var n = document.getElementById('k' + i).value;
        if (n == 'FIRST_NAME')
        {
            fn = fn + 1;
        }
        if (n == 'LAST_NAME')
        {
            ln = ln + 1;
        }
        if (n == 'GENDER')
        {
            gn = gn + 1;
        }
        if (n == 'LANGUAGE')
        {
            pl = pl + 1;
        }
        if (n == 'BIRTHDATE')
        {
            bd = bd + 1;
        }
        if (n == 'USERNAME')
        {
            un = un + 1;
        }
        if (n == 'ESTIMATED_GRAD_DATE')
        {
            es_gd = es_gd + 1;
        }
        if (n == 'START_DATE')
        {
            st_d = st_d + 1;
        }
        if (n == 'MIDDLE_NAME')
        {
            middle_name = middle_name + 1;
        }
        if (n == 'GRADE_ID')
        {
            grade_id = grade_id + 1;
        }
        if (n == 'S_FIRST_NAME')
        {
            sec_first_name = sec_first_name + 1;
        }
        if (n == 'S_LAST_NAME')
        {
            sec_last_name = sec_last_name + 1;
        }
        if (n == 'S_STUDENT_RELATION')
        {
            sec_student_relation = sec_student_relation + 1;
        }

        if (n == 'P_FIRST_NAME')
        {
            pri_first_name = pri_first_name + 1;
        }
        if (n == 'P_LAST_NAME')
        {
            pri_last_name = pri_last_name + 1;
        }
        if (n == 'P_STUDENT_RELATION')
        {
            prim_student_relation = prim_student_relation + 1;
        }
        if (n == 'STREET_ADDRESS_1')
        {
            address = address + 1;
        }
        if (n == 'STATE')
        {
            state = state + 1;
        }
        if (n == 'CITY')
        {
            city = city + 1;
        }
        if (n == 'PHONE')
        {
            phone = phone + 1;
        }

    }
    var error = '';
    if (fn != 1)
    {
        error = 'FIRST NAME';
    }
    if (ln != 1)
    {
        if (error != '')
        {
            error = error + ', LAST NAME';
        }
        else
        {
            error = 'LAST NAME';
        }
    }
    if (gn != 1)
    {
        if (error != '')
        {
            error = error + ', GENDER';
        }
        else
        {
            error = 'GENDER';
        }
    }
    if (pl != 1)
    {
        if (error != '')
        {
            error = error + ', LANGUAGE';
        }
        else
        {
            error = 'LANGUAGE';
        }
    }
    if (bd != 1)
    {
        if (error != '')
        {
            error = error + ', BIRTHDATE';
        }
        else
        {
            error = 'BIRTHDATE';
        }
    }
//    if (un != 1)
//    {
//        if (error != '')
//        {
//            error = error + ', USERNAME';
//        }
//        else
//        {
//            error = 'USERNAME';
//        }
//    }
    if (es_gd != 1)
    {
        if (error != '')
        {
            error = error + ', ESTIMATED GRAD DATE';
        }
        else
        {
            error = 'ESTIMATED GRAD DATE';
        }
    }
    if (st_d != 1)
    {
        if (error != '')
        {
            error = error + ', STUDENT ENROLLMENT DATE';
        }
        else
        {
            error = 'STUDENT ENROLLMENT DATE';
        }
    }

    ///checking for dual value
    var mapping_error = '';
    if (middle_name > 1)
    {
        mapping_error = 'MIDDLE NAME';

    }
    if (city > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'CITY';
        }
        else
        {
            mapping_error = mapping_error + ', CITY';
        }
    }

    if (address > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'ADDRESS';
        }
        else
        {
            mapping_error = mapping_error + ', ADDRESS';
        }
    }

    if (prim_student_relation > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'PRIMARY STUDENT RELATION';
        }
        else
        {
            mapping_error = mapping_error + ', PRIMARY STUDENT RELATION';
        }
    }

    if (pri_last_name > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'PRIMARY LAST NAME';
        }
        else
        {
            mapping_error = mapping_error + ', PRIMARY LAST NAME';
        }
    }

    if (pri_first_name > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'PRIMARY FIRST NAME';
        }
        else
        {
            mapping_error = mapping_error + ', PRIMARY FIRST NAME';
        }
    }

    if (sec_student_relation > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'SECONDARY STUDENT RELATION';
        }
        else
        {
            mapping_error = mapping_error + ', SECONDARY STUDENT RELATION';
        }
    }
    if (sec_last_name > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'SECONDARY LAST NAME';
        }
        else
        {
            mapping_error = mapping_error + ', SECONDARY LAST NAME';
        }
    }
    if (sec_first_name > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'SECONDARY FIRST NAME';
        }
        else
        {
            mapping_error = mapping_error + ', SECONDARY FIRST NAME';
        }
    }
    if (state > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'STATE';
        }
        else
        {
            mapping_error = mapping_error + ', STATE';
        }
    }

    if (phone > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'PHONE';
        }
        else
        {
            mapping_error = mapping_error + ', PHONE';
        }
    }
    if (grade_id > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'GRADE ID';
        }
        else
        {
            mapping_error = mapping_error + ', GRADE ID';
        }
    }
    if (error != '')
    {

        var msg = '<h5 class="m-0"><b>Please map the following fields properly</b></h5>';
        noty({
            dismissQueue: true,
            timeout: 4000,
            text: msg + '<p class="m-0">' + error + '</p>',
            type: 'error',
            layout: 'top'
        });
        //document.getElementById('mapping').innerHTML = '<div class="alert alert-danger alert-bordered"><button type="button" class="close" data-dismiss="alert"><span>�</span><span class="sr-only">Close</span></button><span class="text-semibold">' + msg + '</span><br/>' + error + '</div>';

        document.getElementById(this_button_id).disabled = false;

        return false;
    }
    if (mapping_error != '')
    {

        var msg = '<h5 class="m-0"><b>Please map the following fields properly</b></h5>';
        //document.getElementById('mapping').innerHTML = "<span style='color:red;font-weight:bold;font-size:12px'>" + msg + "<br>" + mapping_error + "</span>";
        noty({
            dismissQueue: true,
            timeout: 4000,
            text: msg + '<p class="m-0">' + mapping_error + '</p>',
            type: 'error',
            layout: 'top'
        });

        document.getElementById(this_button_id).disabled = false;

        return false;
    }

    setTimeout(function() {
        document.getElementById(this_button_id).disabled = true;
    }, 50);
}

function valid_mapping_staff(option, this_DET)
{
    var this_button_id = this_DET.id;

    var sn = 0;
    var fn = 0;
    var ln = 0;
    var pf = 0;
    var un = 0;
    var middle_name = 0;
    var phone = 0;
    var email = 0;
    var is_disable = 0;
    for (var i = 1; i <= option; i++)
    {
        var n = document.getElementById('k' + i).value;
        if (n == 'TITLE')
        {
            sn = sn + 1;
        }
        if (n == 'FIRST_NAME')
        {
            fn = fn + 1;
        }
        if (n == 'LAST_NAME')
        {
            ln = ln + 1;
        }
        if (n == 'PROFILE')
        {
            pf = pf + 1;
        }
        if (n == 'USERNAME')
        {
            un = un + 1;
        }
        if (n == 'MIDDLE_NAME')
        {
            middle_name = middle_name + 1;
        }
        if (n == 'PHONE')
        {
            phone = phone + 1;
        }
        if (n == 'EMAIL')
        {
            email = email + 1;
        }
        if (n == 'IS_DISABLE')
        {
            is_disable = is_disable + 1;
        }

    }
    var error = '';


    if (sn != 1)
    {
        error = 'SALUTATION';
    }
    if (fn != 1)
    {
        if (error != '')
        {
            error = error + ', FIRST NAME';
        }
        else
        {
            error = 'FIRST NAME';
        }
    }
    if (ln != 1)
    {
        if (error != '')
        {
            error = error + ', LAST NAME';
        }
        else
        {
            error = 'LAST NAME';
        }
    }
    if (pf != 1)
    {
        if (error != '')
        {
            error = error + ', PROFILE';
        }
        else
        {
            error = 'PROFILE';
        }
    }
    if (un != 1)
    {
        if (error != '')
        {
            error = error + ', USERNAME';
        }
        else
        {
            error = 'USERNAME';
        }
    }

    //checking for dual values

    var mapping_error = ''
    if (middle_name > 1)
    {
        mapping_error = 'MIDDLE NAME';
    }
    if (phone > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'PHONE';
        }
        else
        {
            mapping_error = mapping_error + ', PHONE';
        }
    }
    if (email > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'EMAIL';
        }
        else
        {
            mapping_error = mapping_error + ', EMAIL';
        }
    }
    if (is_disable > 1)
    {
        if (mapping_error == '')
        {
            mapping_error = 'IS DISABLE';
        }
        else
        {
            mapping_error = mapping_error + ', IS DISABLE';
        }
    }
    if (error != '')
    {
        var msg = '<h5 class="m-0"><b>Please map the following fields properly</b></h5>';
        noty({
            dismissQueue: true,
            timeout: 4000,
            text: msg + '<p class="m-0">' + error + '</p>',
            type: 'error',
            layout: 'top'
        });
        //document.getElementById('mapping').innerHTML = "<span style='color:red;font-weight:bold;font-size:12px'>" + msg + "<br>" + error + "</span>";
        
        document.getElementById(this_button_id).disabled = false;

        return false;
    }
    if (mapping_error != '')
    {
        var msg = '<h5 class="m-0"><b>Please map the following fields properly</b></h5>';
        noty({
            dismissQueue: true,
            timeout: 4000,
            text: msg + '<p class="m-0">' + mapping_error + '</p>',
            type: 'error',
            layout: 'top'
        });
        //document.getElementById('mapping').innerHTML = "<span style='color:red;font-weight:bold;font-size:12px'>" + msg + "<br>" + mapping_error + "</span>";
        
        document.getElementById(this_button_id).disabled = false;

        return false;
    }

    setTimeout(function() {
        document.getElementById(this_button_id).disabled = true;
    }, 50);
}
function ajax_progress(option)
{
    var url = 'modules/tools/DataImportCounter.php?cat=' + option;
    ajax_call_modified(url, data_imp_callback, data_imp_error);
}

function data_imp_callback(data)
{
    document.getElementById('calculating').innerHTML = data;
}

function data_imp_error(err)
{
    alert('Error ' + err);
}
