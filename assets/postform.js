/**
 * Postform JS
 *
 */

var detailPlaceholder='Describe change here...';

function detailChangeHandler(eid) {
  var d=document.getElementById(eid);
  if (d.value == detailPlaceholder) {
    d.style.color='#888';
  } else {
    d.style.color='#000';
  }
}

function detailFocusHandler(eid) {
  var d=document.getElementById(eid);
  if (d.value == detailPlaceholder) {
    d.value = '';
    detailChangeHandler(eid);
  }
}

function outageClick(o) {
  toggleHidden(o, 'outage');
  d = document.getElementById('type');
  if (o.checked) {
    d.value = 'outage';
  } else {
    d.value = 'change';
  }
  document.getElementById('checklist').style.display = 'none';
}

function toggleHidden(o, eid) {
  var d=document.getElementById(eid);
  if (o.href) {
    if (d.style.display == 'block') { d.style.display = 'none'; } else { d.style.display = 'block'; }
    return;
  }
  if (o.checked) { d.style.display = 'block'; } else { d.style.display = 'none'; }
}

window.onload=function(e){
  if (document.getElementById('detail').value.length == 0) {
    document.getElementById('detail').value=detailPlaceholder; detailChangeHandler('detail');
  }
  document.getElementById('sms').value=detailPlaceholder; detailChangeHandler('sms');
  if (document.getElementById('chkOutage').checked) { document.getElementById('outage').style.display='block'; }
  if (document.getElementById('chkEmail').checked) { document.getElementById('maillog').style.display='block'; }
  if (document.getElementById('chkExtra').checked) { document.getElementById('extra').style.display='block'; }
}
