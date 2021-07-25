
function display(x)
{
  var win = window.open();
  for (var i in x) win.document.write(i+' = '+x[i]+'<br>');
}



function showall(parent, child)
{
  var p = document.getElementById(parent);
  var c = document.getElementById(child );

  var top  = (c["position"] == "y") ? p.offsetHeight+2 : 0;
  var left = (c["position"] == "x") ? p.offsetWidth +2 : 0;

  for (; p; p = p.offsetParent)
  {
    top  += p.offsetTop;
    left += p.offsetLeft;
  }

  c.style.position   = "absolute";
  c.style.top        = top +'px';
  c.style.left       = left+'px';
  c.style.visibility = "visible";
}



function show()
{
  var p = document.getElementById(this["parent"]);
  var c = document.getElementById(this["child" ]);

  showall(p.id, c.id);

  clearTimeout(c["timeout"]);
}



function hide()
{
  var c = document.getElementById(this["child"]);

  c["timeout"] = setTimeout("document.getElementById('"+c.id+"').style.visibility = 'hidden'", 333);
}



function menu_on_click()
{
  var p = document.getElementById(this["parent"]);
  var c = document.getElementById(this["child" ]);

  if (c.style.visibility != "visible")
       at_show_aux(p.id, c.id);
  else c.style.visibility = "hidden";

  return false;
}



function createmenu(parent, child, showtype, position, cursor)
{
  var p = document.getElementById(parent);
  var c = document.getElementById(child);

  p["parent"]     = p.id;
  c["parent"]     = p.id;
  p["child"]      = c.id;
  c["child"]      = c.id;
  p["position"]   = position;
  c["position"]   = position;

  c.style.position   = "absolute";
  c.style.visibility = "hidden";

  if (cursor != undefined) p.style.cursor = cursor;

  switch (showtype)
  {
    case "click":
      p.onclick     = menu_on_click;
      p.onmouseout  = hide;
      c.onmouseover = show;
      c.onmouseout  = hide;
      break;
    case "hover":
      p.onmouseover = show;
      p.onmouseout  = hide;
      c.onmouseover = show;
      c.onmouseout  = hide;
      break;
  }
}
