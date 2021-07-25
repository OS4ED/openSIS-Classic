<?php

#**************************************************************************
#  openSIS is a free student information system for public and non-public 
#  schools from Open Solutions for Education, Inc. web: www.os4ed.com
#
#  openSIS is  web-based, open source, and comes packed with features that 
#  include student demographic info, scheduling, grade book, attendance, 
#  report cards, eligibility, transcripts, parent portal, 
#  student portal and more.   
#
#  Visit the openSIS web site at http://www.opensis.com to learn more.
#  If you have question regarding this system or the license, please send 
#  an email to info@os4ed.com.
#
#  This program is released under the terms of the GNU General Public License as  
#  published by the Free Software Foundation, version 2 of the License. 
#  See license.txt.
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#
#  You should have received a copy of the GNU General Public License
#  along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#***************************************************************************************

function DrawTab($title, $link = '', $tabcolor = 'tab_header_bg_active', $textcolor = '', $type = '', $rollover = '') {
    if (substr($title, 0, 1) != '<')
        $title = par_rep("/ /", "&nbsp;", $title);

    $block_table .= "<li class='active' id=tab[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]>";
    if ($link) {
        if (is_array($rollover))
            $rollover = " onmouseover=\"document.getElementById('tab[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]').style.backgroundColor='" . $rollover['tabcolor'] . "';document.getElementById('tab_link[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]').style.color='" . $rollover['textcolor'] . "';\" onmouseout=\"document.getElementById('tab[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]').style.backgroundColor='';document.getElementById('tab_link[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]').style.color='" . $textcolor . "';\" ";
        if (!isset($_REQUEST['_openSIS_PDF']))
            $block_table .= "<A HREF='$link' $rollover id=tab_link[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "] onclick='grabA(this); return false;'>$title</A>";
        else
            $block_table .= '<a href="javascript:void(0);">'.$title.'</a>';
    }
    else {
        if (!isset($_REQUEST['_openSIS_PDF']))
            $block_table .= '<a href="javascript:void(0);">'.$title.'</a>';
        else
            $block_table .= '<a href="javascript:void(0);">'.$title.'</a>';
    }
    $block_table .= "</li>";
    return $block_table;
}

function DrawinactiveTab($title, $link = '', $tabactivecolor = 'tab_header_bg', $textcolor = '', $type = '', $rollover = '') {
    if (substr($title, 0, 1) != '<')
        $title = par_rep("/ /", "&nbsp;", $title);

    $block_table .= "<li id=tab[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]>";
    
    if ($link) {
        if (is_array($rollover))
            $rollover = " onmouseover=\"document.getElementById('tab[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]').style.backgroundColor='" . $rollover['tabcolor'] . "';document.getElementById('tab_link[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]').style.color='" . $rollover['textcolor'] . "';\" onmouseout=\"document.getElementById('tab[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]').style.backgroundColor='';document.getElementById('tab_link[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]').style.color='" . $textcolor . "';\" ";
        if (!isset($_REQUEST['_openSIS_PDF']))
            $block_table .= "<A HREF='$link' $rollover id=tab_link[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "] onclick='grabA(this); return false;'>$title</A>";
        else
            $block_table .= '<a href="javascript:void(0);">'.$title.'</a>';
    }
    else {
        if (!isset($_REQUEST['_openSIS_PDF']))
            $block_table .= '<a href="javascript:void(0);">'.$title.'</a>';
        else
            $block_table .= '<a href="javascript:void(0);">'.$title.'</a>';
    }
    $block_table .= "</li>\n";
    return $block_table;
}

function DrawRoundedRect($title, $link = '', $tabcolor = '#333366', $textcolor = '#FFFFFF', $type = '', $rollover = '') {
    if (substr($title, 0, 1) != '<')
        $title = par_rep("/ /", "&nbsp;", $title);

    $block_table .= "<table border=0 cellspacing=0 cellpadding=0>";
    $block_table .= "  <tr  id=tab[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]>";
    $block_table .= "    <td height=5 width=5><IMG SRC=assets/left_upper_corner.gif border=0></td><td rowspan=3 width=100% class=\"BoxHeading\" valign=middle>";
    if ($link) {
        if (is_array($rollover))
            $rollover = " onmouseover=\"document.getElementById('tab[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]').style.backgroundColor='" . $rollover['tabcolor'] . "';document.getElementById('tab_link[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]').style.color='" . $rollover['textcolor'] . "';\" onmouseout=\"document.getElementById('tab[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]').style.backgroundColor='$tabcolor';document.getElementById('tab_link[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]').style.color='" . $textcolor . "';\" ";
        if (!isset($_REQUEST['_openSIS_PDF']))
            $block_table .= "<A HREF='$link' class=BoxHeading style=$rollover id=tab_link[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]>$title</A>";
        else
            $block_table .= "<font color=$textcolor face=Verdana,Arial,sans-serif size=-2><b>$title</b></font>";
    }
    else {
        if (!isset($_REQUEST['_openSIS_PDF']))
            $block_table .= "<font color=$textcolor>" . $title . "</font>";
        else
            $block_table .= "<font color=$textcolor><b>" . $title . "</b></font>";
    }
    $block_table .= "</td><td height=5 width=5><IMG SRC=assets/right_upper_corner.gif border=0></td>";
    $block_table .= "  </tr>";

    // MIDDLE ROW
    $block_table .= "  <tr  id=tab[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]>";
    $block_table .= "    <td width=5>&nbsp;</td>";
    $block_table .= "<td width=5>&nbsp;</td>";
    $block_table .= "  </tr>";


    // BOTTOM ROW
    $block_table .= "  <tr  id=tab[" . par_rep('/[^a-zA-Z0-9]/', '_', $link) . "]>";
    $block_table .= "    <td height=5 width=5 valign=bottom><IMG SRC=assets/left_lower_corner.gif border=0></td>";
    $block_table .= "<td height=5 width=5 valign=bottom><IMG SRC=assets/right_lower_corner.gif border=0></td>";
    $block_table .= "  </tr>";



    $block_table .= "</table>\n";
    return $block_table;
}

?>