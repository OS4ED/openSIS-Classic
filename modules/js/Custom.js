/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var toggleNewProfile = function () {
    $('#new_profile_div').toggle();
}

var styledCheckboxRadioInit = function () {
    $(".styled").uniform({
        radioClass: 'choice'
    });
}

function divToggle(divId){
    $(divId).hide();
    $(divId+'_element').show();
}