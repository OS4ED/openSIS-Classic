function nameslist(textvalue, id)
{
    if (textvalue != "")
    {
        if (id == 1)
            ajax_call('NamesList.php?str=' + textvalue + '&block_id=' + id, namecheck_match, namecheck_unmatch);
        if (id == 2)
            ajax_call('NamesList.php?str=' + textvalue + '&block_id=' + id, namecheck_matchCC);
        if (id == 3)
            ajax_call('NamesList.php?str=' + textvalue + '&block_id=' + id, namecheck_matchBCC);
    }
}
function namecheck_match(data) {
    var response = data;
    if (response != 0)
    {
        $("#ajax_response").html(response);
        $("#ajax_response").closest('.form-group').addClass('open');
        $("#ajax_response").show();
        return true;
    }
    else
    {
        return false;
    }
}

function namecheck_unmatch(err) {
    alert("Error: " + err);
}

function namecheck_matchCC(data)
{
    var response = data;
    if (response != 0)
    {
        document.getElementById("ajax_response_cc").innerHTML = response;
        return true;
    }
    else
        return false;

}
function namecheck_matchBCC(data)
{
    var response = data;
    if (response != 0)
    {
        document.getElementById("ajax_response_bcc").innerHTML = response;
        return true;
    }
    else
        return false;

}
function a(id, block_id)
{

    if (block_id == 1)
    {
        $("#txtToUser").val(id);
        $("#ajax_response").html("");
        $("#ajax_response").hide();
        $("#ajax_response").removeClass('open');
    }
    if (block_id == 2)
    {
        $("#txtToCCUser").val(id);
        $("#ajax_response_cc").html("");
        $("#ajax_response_cc").hide();
        $("#ajax_response_cc").removeClass('open');
    }
    if (block_id == 3)
    {
        $("#txtToBCCUser").val(id);
        $("#ajax_response_bcc").html("");
        $("#ajax_response_bcc").hide();
        $("#ajax_response_bcc").removeClass('open');
    }

}
function b(index, val, block_id){
    if (block_id == 1)
    {
        var a = $("#txtToUser").val();
        var l = a.slice(0, index);
        $("#txtToUser").val(l + val);
        $("#ajax_response").html("");
        $("#ajax_response").hide();
        $("#ajax_response").removeClass('open');
    }
    if (block_id == 2)
    {
        var a = $("#txtToCCUser").val();
        var l = a.slice(0, index);
        $("#txtToCCUser").val(l + val);
        $("#ajax_response_cc").html("");
        $("#ajax_response_cc").hide();
        $("#ajax_response_cc").removeClass('open');
    }
    if (block_id == 3)
    {
        var a = $("#txtToBCCUser").val();
        var l = a.slice(0, index);
        $("#txtToBCCUser").val(l + val);
        $("#ajax_response_bcc").html("");
        $("#ajax_response_bcc").hide();
        $("#ajax_response_bcc").removeClass('open');
    }

}

function list_of_groups(groupid)
{
    document.getElementById("txtToUser").value = groupid;

}


function groupcheck_match(data) {
    var response = data;
    if (response != 0)
    {
        document.getElementById("txtToUser").value = response;
        return true;
    }
    else
    {
        document.getElementById("txtToUser").value = "";
        return false;
    }
}

function groupcheck_unmatch(err) {
    alert("Error: " + err);
}


function groups(value)
{
    document.getElementById("groupname").value = value;
}
function desc(value)
{
    document.getElementById("groupdescription").value = value;
}