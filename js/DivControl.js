
function show_div(name, mp_id) {
    if (document.getElementById(name).checked) {

        document.getElementById("grade_div").style.display = "block";
        if (document.getElementById('divtables[' + mp_id + '][DOES_EXAM]'))
            document.getElementById('divtables[' + mp_id + '][DOES_EXAM]').innerHTML = '<input type=checkbox name=tables[' + mp_id + '][DOES_EXAM] id=tables[' + mp_id + '][DOES_EXAM]  value=Y>';

        document.getElementById('tables[' + mp_id + '][DOES_EXAM]').disabled = false;
    }
    if (document.getElementById(name).checked == false) {
        if (document.getElementById('divtables[' + mp_id + '][DOES_EXAM]'))
            document.getElementById('divtables[' + mp_id + '][DOES_EXAM]').innerHTML = '<input type=checkbox name=tables[' + mp_id + '][DOES_EXAM] id=tables[' + mp_id + '][DOES_EXAM]>';
        document.getElementById('tables[' + mp_id + '][DOES_EXAM]').checked = false;
        document.getElementById('tables[' + mp_id + '][DOES_EXAM]').disabled = true;
        /*document.getElementById('date_3').style.display = 'block';
        document.getElementById('date_div_3').style.display = 'none';
        document.getElementById('date_4').style.display = 'block';
        document.getElementById('date_div_4').style.display = 'none';*/

        document.getElementById('monthSelect_date_3').value = '';
        document.getElementById('daySelect_date_3').value = '';
        document.getElementById('yearSelect_date_3').value = '';
        document.getElementById('date_3').value = ''

        document.getElementById('monthSelect_date_4').value = '';
        document.getElementById('daySelect_date_4').value = '';
        document.getElementById('yearSelect_date_4').value = '';
        document.getElementById('date_4').value = '';

        document.getElementById("grade_div").style.display = "none";

    }
}

function selectFile(input) {


    if (input.files[0].type != '' && (input.files[0].type == 'image/png' || input.files[0].type == 'image/jpeg' || input.files[0].type == 'image/jpg' || input.files[0].type == 'image/tiff' || input.files[0].type == 'image/bmp')) {
        if (input.files && input.files[0]) {

            var reader = new FileReader();

            reader.onload = function (e) {
                document.getElementById('div_img').innerHTML = '<img src="" alt="" class="cropper" id="demo-cropper-image">';
                document.getElementById('demo-cropper-image').src = e.target.result;
                $('#modal_crop_image').modal('show');
                $('#modal_crop_image').on('shown.bs.modal', function () {
                    // Define variables
                    var $cropper = $(".cropper"),
                        $image = $('#demo-cropper-image'),
                        $download = $('#download'),
                        $dataX = $('#dataX'),
                        $dataY = $('#dataY'),
                        $dataHeight = $('#dataHeight'),
                        $dataWidth = $('#dataWidth'),
                        $dataScaleX = $('#dataScaleX'),
                        $dataScaleY = $('#dataScaleY'),
                        options = {
                            aspectRatio: 1,
                            preview: '.preview',
                            crop: function (e) {
                                $dataX.val(Math.round(e.x));
                                $dataY.val(Math.round(e.y));
                                $dataHeight.val(Math.round(e.height));
                                $dataWidth.val(Math.round(e.width));
                                $dataScaleX.val(e.scaleX);
                                $dataScaleY.val(e.scaleY);

                                var dataURL = $cropper.cropper('getCroppedCanvas', { width: 300, height: 300 }).toDataURL();
                                var imgblob = dataURL.split(',');
                                $('#imgblob').val(imgblob[1].trim());

                            }
                        };

                    // Initialize cropper with options
                    $cropper.cropper(options);


                });
            }

            reader.readAsDataURL(input.files[0]);


        }
    }
    else
        document.getElementById('divErr').innerHTML = '<div class="alert alert-danger alert-styled-left alert-bordered">Unsupported file format, use common image file like - jpeg, png, tiff, bmp etc.</div>';
    //    if (selectedFile.match(/fakepath/)) {
    //        // update the file-path text using case-insensitive regex
    //        selectedFile = selectedFile.replace(/C:\\fakepath\\/i, '');
    //        
    //    }
    //    document.getElementById("uploadFile").innerHTML = selectedFile;
}

function hide_search_div() {
    $("#searchdiv").hide();
    $("#advancedSearchForStudentDiv, #advancedSearchDivForStudents, #attendanceAdvancedSearchDivForStudents").css({ 'display': 'inline-block' });
}

function hide_search_div1() {
    $("#searchdiv1").hide();
    $("#advancedSearchDivForStudentsFilters, #advancedFilterDivForStudents").css({ 'display': 'inline-block' });
}

function show_search_div() {
    $("#searchdiv").show();
    $("#advancedSearchForStudentDiv, #advancedSearchDivForStudents, #attendanceAdvancedSearchDivForStudents").hide();
}

function show_search_div1() {
    $("#searchdiv1").show();
    $("#advancedSearchDivForStudentsFilters, #advancedFilterDivForStudents").hide();
}

function hidediv() {
    if (document.getElementById) {
        document.getElementById("hideShow").style.display = "none";
    }
}

function showdiv() {
    if (document.getElementById) {
        document.getElementById("hideShow").style.display = "block";
    }
}

function prim_hidediv() {
    if (document.getElementById) {
        document.getElementById("prim_hideShow").style.display = "none";
    }
}

function prim_showdiv() {
    if (document.getElementById) {
        document.getElementById("prim_hideShow").style.display = "block";
    }
}

function sec_hidediv() {
    if (document.getElementById) {
        document.getElementById("sec_hideShow").style.display = "none";
    }
}

function sec_showdiv() {
    if (document.getElementById) {
        document.getElementById("sec_hideShow").style.display = "block";
    }
}

function addn_hidediv() {
    if (document.getElementById) {
        document.getElementById("addn_hideShow").style.display = "none";
    }
}

function addn_showdiv() {
    if (document.getElementById) {
        document.getElementById("addn_hideShow").style.display = "block";
    }
}
function confirmAction() {
    chk = 'n';
    var option = "";
    if (document.run_schedule.test_mode.checked == false) {

        if (document.run_schedule.delete_mode.checked == false) {

            chk = 'y';
        }
        else
            var option = "delete current schedules ? ";
    }
    else
        var option = "run the scheduler to schedule unscheduled requests? ";
    if (chk == 'y') {
        var d = $('divErr');
        var err = "Please select one options.";
        d.innerHTML = "<b><font color=red>" + err + "</font></b>";
        return false;
    }
    else {
        if (confirm("Do you really want to " + option) == true)
            return true;
        else
            return false;
    }
}

function showhidediv(it, box) {
    if (document.getElementById) {



        var vis = (box.checked) ? "block" : "none";

        document.getElementById(it).style.display = vis;
    }
}

function system_wide(val) {
    var check_id = 'all_day_' + val;
    if (document.getElementById(check_id).checked == false) {
        document.getElementById('syswide_holi_' + val).style.display = "block";
    }
    else {
        document.getElementById('syswide_holi_' + val).style.display = "none";
    }
}
function show_this_msg(tag_id, msg_id, option) {
    document.getElementById(tag_id).disabled = true;

    if (option == "calendar")
        option = "the calendar";
    if (option == "grade")
        option = "the grade scale";
    document.getElementById(msg_id).innerHTML = '<font style="color:red"><b>Cannot change ' + option + ' as this course period has association</b></font>';
}
function show_home_error() {
    document.getElementById('divErr').innerHTML = "<b><font color=red>Please provide home address first.</font></b>";
}
function set_check_value(val, name) {
    if(document.getElementById('IS_EMERGENCY_HIDDEN')){
        if (val.checked == false) {
            document.getElementById(name).value = 'N';
            document.getElementById('IS_EMERGENCY_HIDDEN').value = "N";
        }
        else
            document.getElementById('IS_EMERGENCY_HIDDEN').value = "Y";
    }
}
function portal_toggle(id) {

    if (document.getElementById('portal_' + id).checked == true) {
        document.getElementById('portal_div_' + id).style.display = "block";
        document.getElementById('portal_hidden_div_' + id).innerHTML = '';
    }
    if (document.getElementById('portal_' + id).checked == false) {
        document.getElementById('portal_div_' + id).style.display = "none";
        if (id == '1')
            document.getElementById('portal_hidden_div_' + id).innerHTML = '<input type=hidden name="values[student_contacts][PRIMARY][USER_NAME]" value=""/><input type=hidden name="values[student_contacts][PRIMARY][PASSWORD]" value=""/>';
        if (id == '2')
            document.getElementById('portal_hidden_div_' + id).innerHTML = '<input type=hidden name="values[student_contacts][SECONDARY][USER_NAME]" value=""/><input type=hidden name="values[student_contacts][SECONDARY][PASSWORD]" value=""/>';
    }
}
function show_span(id, val) {
    if (val == 'Y')
        document.getElementById(id).style.display = "block";
    if (val == 'N')
        document.getElementById(id).style.display = "none";
}
function show_cc() {
    if (document.getElementById('cc').style.display == 'none')
        document.getElementById('cc').style.display = 'block';
    else
        document.getElementById('cc').style.display = 'none'

}
function show_bcc() {
    if (document.getElementById('bcc').style.display == 'none')
        document.getElementById('bcc').style.display = 'block';
    else
        document.getElementById('bcc').style.display = 'none'

}
function attachfile(this_val) {

    document.getElementById('del' + this_val).style.display = 'block';
    if (this_val == 1)
        document.getElementById('attach1').style.display = 'block';
}
function appendFile() {
    var counter = document.getElementById('counter').value;
    var counter_split = counter.split(',');

    if (counter_split.length == 1) {
        document.getElementById('counter').value = '1,2';
        var count_id = 2;
    }
    if (counter_split.length == 2) {
        if (counter_split[0] == 1 && counter_split[1] == 2)
            var count_id = 3;

        if (counter_split[0] == 1 && counter_split[1] == 3)
            var count_id = 2;

        if (counter_split[0] == 2 && counter_split[1] == 3)
            var count_id = 1;

        document.getElementById('counter').value = '1,2,3';

    }

    var newtr = document.createElement('div');
    newtr.id = 'div' + count_id;
    newtr.innerHTML = '<div class="form-group clearfix"><div class="col-md-4"><input type="file" name="f[]" id="up' + count_id + '" onchange="attachfile(' + count_id + ');" multiple/></div><div id="del' + count_id + '" class="col-md-8" style="display: none;"><input type="button" value="Clear" class="btn btn-danger btn-xs" onclick="clearfile(' + count_id + ')" /></div></div>';
    document.getElementById('append_tab').appendChild(newtr);


    if (document.getElementById('counter').value == '1,2,3')
        document.getElementById('attach1').style.display = 'none';


}
function clearfile(this_id) {
    var oFiles = document.getElementById("up" + this_id);

    oFiles.value = "";
    document.getElementById('del' + this_id).style.display = 'none';

    var counter = document.getElementById('counter').value;
    var counter_split = counter.split(',');

    if (counter_split.length == 1) {
        document.getElementById('counter').value = this_id;
    }
    if (counter_split.length == 2) {
        if (counter_split[0] != this_id)
            document.getElementById('counter').value = counter_split[0];

        if (counter_split[1] != this_id)
            document.getElementById('counter').value = counter_split[1];

    }
    if (counter_split.length == 3) {
        var counter_val = new Array();
        var i = 0;
        if (counter_split[0] != this_id) {
            counter_val[i] = counter_split[0];
            i++;
        }
        if (counter_split[1] != this_id) {
            counter_val[i] = counter_split[1];
            i++;
        }
        if (counter_split[2] != this_id) {
            counter_val[i] = counter_split[2];
            i++;
        }
        document.getElementById('counter').value = counter_val.join();
    }

    var counter = document.getElementById('counter').value;
    var row = document.getElementById('div' + this_id);
    row.parentNode.removeChild(row);
    if (this_id == 1) {
        if (counter == '1') {
            var newtr = document.createElement('div');
            newtr.id = 'div1';
            newtr.innerHTML = '<div class="form-group clearfix"><div class="col-md-4"><input type="file" name="f[]" id="up1" onchange="attachfile(1);" multiple/></div><div id="del1" style="display:none;" class="col-md-8"><input type="button" value="Clear" class="btn btn-danger btn-xs" onclick="clearfile(1)" /></div></div>';
            document.getElementById('append_tab').appendChild(newtr);
            document.getElementById('attach1').style.display = 'none';
        }
        else {
            document.getElementById('attach1').style.display = 'block';
        }
    }
    if (this_id == 2) {
        if (counter == '2') {
            var newtr = document.createElement('div');
            newtr.id = 'div1';
            newtr.innerHTML = '<div class="form-group clearfix"><div class="col-md-4"><input type="file" name="f[]" id="up1" onchange="attachfile(1);" multiple/></div><div id="del1" style="display:none;" class="col-md-8"><input type="button" value="Clear" class="btn btn-danger btn-xs" onclick="clearfile(1)" /></div></div>';
            document.getElementById('append_tab').appendChild(newtr);
            document.getElementById('attach1').style.display = 'none';
        }
        else
            document.getElementById('attach1').style.display = 'block';
    }
    if (this_id == 3) {
        if (counter == '3') {
            var newtr = document.createElement('div');
            newtr.id = 'div1';
            newtr.innerHTML = '<div class="form-group clearfix"><div class="col-md-4"><input type="file" name="f[]" id="up1" onchange="attachfile(1);" multiple/></div><div id="del1" style="display:none;" class="col-md-8"><input type="button" value="Clear" class="btn btn-danger btn-xs" onclick="clearfile(1)" /></div></div>';
            document.getElementById('append_tab').appendChild(newtr);
            document.getElementById('attach1').style.display = 'none';
        }
        else
            document.getElementById('attach1').style.display = 'block';
    }
}

function cp_toggle(chk) {
    if (chk.checked == true) {
        document.getElementById(chk.id + '_period').disabled = false;

        document.getElementById(chk.id + '_room').disabled = false;
        document.getElementById(chk.id + '_does_attendance').disabled = false;

    }
    else {
        document.getElementById(chk.id + '_period').value = '';
        document.getElementById(chk.id + '_period').disabled = true;
        document.getElementById(chk.id + '_period_time').innerHTML = '';
        document.getElementById(chk.id + '_room').value = '';
        document.getElementById(chk.id + '_room').disabled = true;
        document.getElementById(chk.id + '_does_attendance').checked = false;
        document.getElementById(chk.id + '_does_attendance').disabled = true;
    }
}

function mp_range_toggle(rad) {
    if (rad.checked == true && rad.id == 'preset') {
        document.getElementById("mp_range").style.display = 'block';
        document.getElementById("date_range").style.display = 'none';
        document.getElementById("select_range").style.display = 'block';
        document.getElementById("select_mp").style.display = 'none';
    }
    else {
        document.getElementById("mp_range").style.display = 'none';
        document.getElementById("date_range").style.display = 'block';
        document.getElementById("select_range").style.display = 'none';
        document.getElementById("select_mp").style.display = 'block';
    }
}

function reset_schedule() {
    document.getElementById("meeting_days").innerHTML = '';
    document.getElementById("fixed_schedule").checked = false;
    document.getElementById("variable_schedule").checked = false;
    document.getElementById("blocked_schedule").checked = false;

}
function show_this_msg(tag_id, msg_id, option) {
    document.getElementById(tag_id).disabled = true;

    if (option == "calendar")
        option = "the calendar";
    if (option == "grade")
        option = "the grade scale";
    document.getElementById(msg_id).innerHTML = '<font style="color:red"><b>Cannot change ' + option + ' as this course period has association</b></font>';
}

function showDiv() {
    if (document.getElementById) {
        document.getElementById("attach").style.display = "block";
    }
}
function showDiv1() {
    if (document.getElementById) {
        document.getElementById("attach1").style.display = "block";
    }
}
function show_this_msg(msg) {
    document.getElementById('divErr').innerHTML = "<font style='color:red'>" + msg + "</font>";
}
function toggle_div_visibility(param, val, json_field) {

    var field_ids = document.getElementById(json_field).value;
    field_ids = JSON.parse(field_ids);


    if (val.checked == true) {
        $('#' + param).css({ 'display': 'inline-block' });
    } else {
        for (var i = 0; i < field_ids.length; i++)
            document.getElementById(field_ids[i]).checked = false;
        document.getElementById(param).style.display = "none";

    }
}
function toggle_div_visibility_mod(param, field_id) {
    if (param.checked == true)
        document.getElementById(field_id).style.display = "block";
    else
        document.getElementById(field_id).style.display = "none";
}
function toggle_course_weight(param, cp_id) {

    if (param.value != '') {
        if (cp_id != 0) {
            var child = document.getElementById("divtables[course_periods][" + cp_id + "][COURSE_WEIGHT]").children[0];
            var clickEvent = new MouseEvent("click");
            child.dispatchEvent(clickEvent);

            var child1 = document.getElementById("divtables[course_periods][" + cp_id + "][DOES_BREAKOFF]").children[0];
            var clickEvent1 = new MouseEvent("click");
            child1.dispatchEvent(clickEvent1);
        }

        document.getElementById("course_weight_id").disabled = false;
        document.getElementById("course_breakoff_id").disabled = false;
    }
    else {
        if (cp_id != 0) {
            var child = document.getElementById("divtables[course_periods][" + cp_id + "][COURSE_WEIGHT]").children[0];
            var clickEvent = new MouseEvent("click");
            child.dispatchEvent(clickEvent);
            var child1 = document.getElementById("divtables[course_periods][" + cp_id + "][DOES_BREAKOFF]").children[0];
            var clickEvent1 = new MouseEvent("click");
            child1.dispatchEvent(clickEvent1);
        }
        document.getElementById("course_weight_id").checked = false;
        document.getElementById("course_weight_id").disabled = true;
        document.getElementById("course_breakoff_id").checked = false;
        document.getElementById("course_breakoff_id").disabled = true;
    }
}
function ListOutputJS() {
    var location = document.getElementById("search_path").value;
    var lo_search = document.getElementById("LO_search").value;
    document.location.href = location + '&LO_search=' + encodeURIComponent(lo_search).replace(/%20/g, '+');
}
function cleanModal(id) {
    var obj = document.getElementById(id);
    obj.innerHTML = '';

    if (document.getElementById('conf_div1')) {
        document.getElementById('conf_div1').innerHTML = '';
    }
}
function cpPasteField(title, course_period_id) {

    var obj = document.getElementById('course_div');
    var field = title + '<INPUT type=hidden name=w_course_period_id value=' + course_period_id + '>';
    //    var obj_hidden = document.getElementById('hidden_tag_cp_id');
    obj.innerHTML = field;
    $('#modal_default').modal('hide');
    $('.modal-backdrop').remove();
}
function requestPasteField(title, course_id) {
    var obj = document.getElementById('request_div');
    var field = title + '<INPUT type=hidden name=request_course_id value=' + course_id + '>';
    // var obj_hidden = document.getElementById('hidden_tag_cp_id');
    obj.innerHTML = field;
    $('#course_div').text(title);
    $('#modal_default_request').modal('hide');
    $('.modal-backdrop').remove();
}
function checkChecked(id1, id2) {
    if (document.getElementById(id2).checked == false)
        document.getElementById(id1).checked = false;
}
function turnCheckOff(id1, id2) {
    if (document.getElementById(id2).checked == false)
        document.getElementById(id1).checked = false;
}

function setHiddenCheckbox(name, elem, unique_id) {
    //    alert(name+' '+elem.checked+' '+unique_id);
    if (elem.checked == true)
        $('#hidden_checkboxes').append("<input type=hidden name='" + name + "' value='Y' data-checkbox-hidden-id='" + unique_id + "' />");
    else
        $('[data-checkbox-hidden-id=' + unique_id + ']').remove();
}

function setHiddenCheckboxStudents(name, elem, unique_id, value = undefined) {
    if (value === undefined) value = unique_id;
    if (elem.checked == true)
        $('#hidden_checkboxes').append("<input type=hidden name='" + name + "' value='" + value + "' data-checkbox-hidden-id='" + unique_id + "' />");
    else
        $('[data-checkbox-hidden-id=' + unique_id + ']').remove();
}

function checkAllDtMod(elem, name, staticValue = undefined) {
    var value = staticValue;
    if (document.getElementById('checked_all')) {
        if (elem.checked == true) {
            document.getElementById('checked_all').value = 'true';
        } else {
            document.getElementById('checked_all').value = 'false';
        }
    }
    var res_length = document.getElementById('res_length').value;
    var res_len = document.getElementById('res_len').value;
    var unique_id = res_len.split(',');
    //    alert(name+' '+elem.checked+' '+unique_id);

    if (document.getElementById('all_stu_res')) {
        var all_stu_res = document.getElementById('all_stu_res').value;
        var all_stu_ids = all_stu_res.split(',');
        for (var i = 0; i < all_stu_ids.length; i++) {
            if (elem.checked == true) {
                if (staticValue === undefined) value = all_stu_ids[i];
                $('#hidden_checkboxes').append("<input type=hidden name='" + name + "[" + all_stu_ids[i] + "]' value='" + value + "' data-checkbox-hidden-id='" + all_stu_ids[i] + "' />");
                if (document.getElementById(all_stu_ids[i])) {
                    document.getElementById(all_stu_ids[i]).checked = true;
                }
            } else {
                $('[data-checkbox-hidden-id=' + all_stu_ids[i] + ']').remove();
                if (document.getElementById(all_stu_ids[i])) {
                    document.getElementById(all_stu_ids[i]).checked = false;
                }
            }
        }
    } else {
        for (var i = 0; i < res_length; i++) {
            if (elem.checked == true) {
                if (document.getElementById(unique_id[i])) {
                    if (staticValue === undefined) value = unique_id[i];
                    $('#hidden_checkboxes').append("<input type=hidden name='" + name + "[" + unique_id[i] + "]' value='" + value + "' data-checkbox-hidden-id='" + unique_id[i] + "' />");
                    document.getElementById(unique_id[i]).checked = true;
                }
            } else {
                if (document.getElementById(unique_id[i])) {
                    $('[data-checkbox-hidden-id=' + unique_id[i] + ']').remove();
                    document.getElementById(unique_id[i]).checked = false;
                }
            }
        }
    }
}

function checkAllDtModAddDrop(elem, name, staticValue = undefined) {
    var value = staticValue;
    if (document.getElementById('checked_all')) {
        if (elem.checked == true) {
            document.getElementById('checked_all').value = 'true';
        } else {
            document.getElementById('checked_all').value = 'false';
        }
    }

    if (document.getElementById('all_stu_res')) {
        var all_stu_res = document.getElementById('all_stu_res').value;
        var all_stu_ids = all_stu_res.split(',');
        for (var i = 0; i < all_stu_ids.length; i++) {
            if (elem.checked == true) {
                if (staticValue === undefined) value = all_stu_ids[i];
                $('#hidden_checkboxes').append("<input type=hidden name='" + name + "[" + all_stu_ids[i] + "]' value='" + value + "' data-checkbox-hidden-id='" + all_stu_ids[i] + "' />");
                if (document.getElementById(all_stu_ids[i])) {
                    document.getElementById(all_stu_ids[i]).checked = true;
                }
            } else {
                $('[data-checkbox-hidden-id=' + all_stu_ids[i] + ']').remove();
                if (document.getElementById(all_stu_ids[i])) {
                    document.getElementById(all_stu_ids[i]).checked = false;
                }
            }
        }
    }
}

function checkAllDtMod2(elem, name, staticValue = undefined) {
    var value = staticValue;
    if (document.getElementById('checked_all')) {
        if (elem.checked == true) {
            document.getElementById('checked_all').value = 'true';
        } else {
            document.getElementById('checked_all').value = 'false';
        }
    }
    var res_length = document.getElementById('res_length').value;
    var res_len = document.getElementById('res_len').value;
    var unique_id = res_len.split(',');

    if (document.getElementById('all_stu_res')) {
        console.log('all_stu_res found');
        var all_stu_res = document.getElementById('all_stu_res').value;
        var all_stu_ids = all_stu_res.split(',');
        for (var i = 0; i < all_stu_ids.length; i++) {
            if (elem.checked == true) {
                if (staticValue === undefined) value = all_stu_ids[i];
                $('#hidden_checkboxes').append("<input type=hidden name='" + name + "[" + all_stu_ids[i] + "]' value='" + value + "' data-checkbox-hidden-id='" + all_stu_ids[i] + "' />");
                if (document.getElementById(all_stu_ids[i])) {
                    // document.getElementById(all_stu_ids[i]).checked=true;
                    // window.$('#'+all_stu_ids[i]).attr("checked",true);
                    $(".student_label_cbx").prop('checked', true);
                    // alert(all_stu_ids[i]);
                }
            } else {
                $('[data-checkbox-hidden-id=' + all_stu_ids[i] + ']').remove();
                if (document.getElementById(all_stu_ids[i])) {
                    // document.getElementById(all_stu_ids[i]).checked=false;
                    $(".student_label_cbx").prop('checked', false);
                }
            }
        }
    } else {
        for (var i = 0; i < res_length; i++) {
            if (elem.checked == true) {
                if (document.getElementById(unique_id[i])) {
                    if (staticValue === undefined) value = unique_id[i];
                    $('#hidden_checkboxes').append("<input type=hidden name='" + name + "[" + unique_id[i] + "]' value='" + value + "' data-checkbox-hidden-id='" + unique_id[i] + "' />");
                    // document.getElementById(unique_id[i]).checked=true;
                    // window.$('#'+unique_id[i]).attr("checked",true);
                    $(".student_label_cbx").prop('checked', true);
                    // alert(unique_id[i]);
                }
            } else {
                if (document.getElementById(unique_id[i])) {
                    $('[data-checkbox-hidden-id=' + unique_id[i] + ']').remove();
                    // document.getElementById(unique_id[i]).checked=false;
                    $(".student_label_cbx").prop('checked', false);
                }
            }
        }
    }
}

function addseccheck_button() {
    if (document.getElementById('values[people][SECONDARY][RELATIONSHIP]').value != '') {
        document.getElementById('rss').checked = true;
    } else {
        document.getElementById('rss').checked = false;
    }

}

function hide_filter_modal() {
    window.$('#modal_filter_edit').modal('toggle');
}

function closeThisModal(modal_ID = false) {
    if (modal_ID != '') {
        window.$('#' + modal_ID).modal('toggle');
    }
}

function clickOnFileInput() {
    document.getElementById("upfile").click();
}

function selectedFilesRail(inputFileID) {

    var fileobj = [];
    if (inputFileID && inputFileID != '') {
        setFilesRail(document.getElementById(inputFileID).files);
    }
}

function setFilesRail(files) {
    fileobj = fileobj.concat(...files);

    printFilesRail();
}

function printFilesRail() {
    var fileListElement = document.querySelector("#areaFileRail ul");

    var fileList = '';
    var storeFiles = '';
    var fileCounter = 0;

    fileobj.map((file, index) => {
        // fileList += `<li class="m-t-5"><button class="btn btn-danger btn-icon btn-xs m-r-10" type="button" onclick="removeFromFilesRail(${index})"><i class="fa fa-times"></i></button>${file.name}</li>`;
        fileList += `<li class="m-t-10"><i class="fa fa-file-o text-primary m-r-10"></i>${file.name}</li>`;

        fileCounter++;
    });
    console.log(fileobj);

    fileListElement.innerHTML = fileList;

    if (fileCounter > 0) {
        $("#nofiles").hide();
    } else {
        $("#nofiles").show();
    }
    fileobj = [];
}

function removeFromFilesRail(index) {
    fileobj.splice(index, 1);

    printFilesRail();
}

function printViewSchedule(exportFileName, viewMode) {
    var printableTable = '';

    if (viewMode == 'week_view')
        printableTable = 'results';
    else
        printableTable = 'export_res_table';

    var doc = new jsPDF('l', 'pt', 'a3');

    var PDFWidth = doc.internal.pageSize.getWidth();
    var PDFWidthMod = Math.round((PDFWidth / 6.0));

    var lineSeparator = '';

    for (var lS = 1; lS < PDFWidthMod; lS++)
        lineSeparator += '_';

    doc.setProperties({ title: exportFileName + '.pdf' });
    doc.setFont("roboto", "regular");

    doc.autoTable({
        html: '#_pdf_header',
        theme: "plain",
        margin: {
            top: 13,
            right: 11,
            left: 11
        }
    });

    doc.text(lineSeparator, 14, 50);

    doc.autoTable({
        html: '#_pdf_header2',
        theme: "plain",
        margin: {
            left: 11
        },
        startY: 60,
    });

    doc.autoTable({
        html: '#' + printableTable,
        margin: {
            right: 14,
            left: 14
        },
        startY: doc.lastAutoTable.finalY + 10,
        headStyles: {
            lineWidth: 0.20,
            lineColor: [0, 0, 0],
            textColor: [0, 0, 0],
            fillColor: [211, 211, 211]
        },
        bodyStyles: {
            lineWidth: 0.20,
            lineColor: [0, 0, 0],
            textColor: [0, 0, 0],
            cellWidth: 'auto'
        }
    });

    // doc.save(exportFileName + '.pdf');
    doc.output('dataurlnewwindow');
    // let dataSrc = doc.output("datauristring");
    // let win = window.open("", "myWindow");
    // win.document.write('<html><head><title>openSIS Student Information System</title><link rel="shortcut icon" href="../favicon.ico"></head><body><iframe src="' + dataSrc + '" style="min-height:100vh;width:100%" frameborder="0"></iframe></body></html>');
}

function togglePasswordView(thisElement, targetElement) {
    $(thisElement).toggleClass("fa-eye-slash fa-eye");
    var inputTarget = document.getElementById(targetElement);
    if (inputTarget.getAttribute("type") === "password") {
        inputTarget.setAttribute("type", "text");
    } else {
        inputTarget.setAttribute("type", "password");
    }
}

function triggerclassListExcel(thisElement, actionValue){
    thisElement.form.action += "&excelReport=" + actionValue;
    thisElement.form.submit();
}

function triggerAdvancedReportExcel(thisElement, actionValue){
    thisElement.form.action += "&excelReport=" + actionValue;
    thisElement.form.submit();
}

function autoset_gradelevel(thisElement) {
    let selectedMP = thisElement.value;
    let selectedMPText = thisElement.selectedOptions[0].innerText;

    let gradelevelData = document.getElementById("gradelevelData").innerText;

    let gradelevelDataParsed = JSON.parse(gradelevelData);

    if (gradelevelDataParsed[selectedMP] && Object.keys(gradelevelDataParsed[selectedMP]).length !== 0) {
        document.getElementById("SMS_GRADE_LEVEL").value = gradelevelDataParsed[selectedMP].grade_level;
        document.getElementById("SMS_GRADE_LEVEL").readOnly = true;

        if (document.getElementById("gradeLevelHelp")) {
            window.$("#gradeLevelHelp").html(`<div id="gradeLevelHelp" class="help-block">You cannot change the Grade Level as the student is already in this Grade Level in ${gradelevelDataParsed[selectedMP].school_name} for ${selectedMPText}.</div>`);
        }
        else {
            window.$("#gradeLevelArea").append(`<div id="gradeLevelHelp" class="help-block">You cannot change the Grade Level as the student is already in this Grade Level in ${gradelevelDataParsed[selectedMP].school_name} for ${selectedMPText}.</div>`);
        }
    }
    else {
        document.getElementById("SMS_GRADE_LEVEL").value = "";
        document.getElementById("SMS_GRADE_LEVEL").readOnly = false;

        if (document.getElementById("gradeLevelHelp")) {
            document.getElementById("gradeLevelHelp").remove();
        }
    }
}