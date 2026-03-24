<?php ?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>JAM Lying-In Clinic - My Child's Record</title>
<link rel="stylesheet" href="docbbyprof.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="container">
  <div class="sidebar collapsed" id="sidebar">
    <div class="sidebar-header">Menu</div>
    <button class="side-item" id="backBtn">← Back</button>
    <button class="side-item" id="editProfileBtn">✎ Edit Profile</button>
  </div>
  <div class="topbar">
    <button class="sidebar-toggle" id="sidebarToggle">☰</button>
    <div class="title">JAM Lying-In Clinic - My Child's Record</div>
  </div>
  <div class="columns one">
    <div class="col main">
      <div class="profile-card horizontal">
        <div class="profile-left">
          <div class="avatar circle" id="avatar"></div>
          <input type="file" id="avatarInput" accept="image/*" class="hidden-file">
          <div class="baby-name" id="babyName">Name</div>
          <div class="name-edit-actions" id="nameEditActions">
          </div>
          <div class="photo-edit-actions" id="photoEditActions">
            <button id="changePhoto" class="secondary">Change Photo</button>
          </div>
        </div>
        <div class="profile-right">
          <div class="profile-grid info-grid" id="profileGrid">
            <div class="row" data-field="gender"><span class="label">Gender</span><span class="value"></span></div>
            <div class="row" data-field="dob"><span class="label">Date of Birth</span><span class="value"></span></div>
            <div class="row" data-field="tob"><span class="label">Time of Birth</span><span class="value"></span></div>
            <div class="row" data-field="pob"><span class="label">Place of Birth</span><span class="value"></span></div>
            <div class="row" data-field="height"><span class="label">Birth Height</span><span class="value"></span></div>
            <div class="row" data-field="weight"><span class="label">Birth Weight</span><span class="value"></span></div>
            <div class="row" data-field="mother"><span class="label">Mother's Name</span><span class="value"></span></div>
            <div class="row" data-field="father"><span class="label">Father's Name</span><span class="value"></span></div>
            <div class="row" data-field="pediatrician"><span class="label">Pediatrician</span><span class="value"></span></div>
            <div class="row" data-field="pediatrician_contact"><span class="label">Pediatrician's Contact Number</span><span class="value"></span></div>
          </div>
          <div class="edit-actions" id="editActions">
            <button id="saveProfile" class="primary">Save Profile</button>
            <button id="cancelEdit" class="secondary">Cancel</button>
          </div>
        </div>
      </div>
      <div class="content-card">
      <div class="tabs">
        <button class="tab-btn active" data-tab="records">Records</button>
        <button class="tab-btn" data-tab="immunization">Immunization</button>
        <button class="tab-btn" data-tab="milestones">Milestones</button>
      </div>

      <div id="records" class="tab-pane active">
        <div class="records-search">
          <label>Search date</label>
          <input type="date" id="recordSearchDate">
        </div>
        <div id="recordsBody" class="records-list">
          <div class="form-grid">
            <div class="field"><span class="field-label">Visit date</span><input type="date"></div>
            <div class="field"><span class="field-label">Age</span><input type="text" placeholder="months"></div>
            <div class="field"><span class="field-label">Weight</span><input type="text" placeholder="kg"></div>
            <div class="field"><span class="field-label">Head circumference</span><input type="text" placeholder="cm"></div>
            <div class="field"><span class="field-label">Chest circumference</span><input type="text" placeholder="cm"></div>
            <div class="field"><span class="field-label">Length</span><input type="text" placeholder="cm"></div>
            <div class="field wide"><span class="field-label">Doctor's instructions</span><textarea placeholder="notes" rows="2" style="resize:vertical;"></textarea></div>
            <div class="field"><span class="field-label">Date of next visit</span><input type="date"></div>
          </div>
        </div>
        <div class="actions">
          <button id="saveRecords">Save</button>
        </div>
      </div>

      <div id="immunization" class="tab-pane">
        <div class="accordion" id="immunizationAccordion"></div>
        <div class="actions">
          <button id="openCustomModal">Add custom vaccine</button>
          <button id="saveImmunization">Save</button>
        </div>
        <div id="customVaccineModal" class="modal hidden">
          <div class="modal-content">
            <div class="modal-title">Add Custom Vaccine</div>
            <div class="modal-grid">
              <div class="field"><span class="field-label">Vaccine name/type</span><input type="text" id="customVaccineName"></div>
              <div class="field"><span class="field-label">Number of doses</span><input type="number" id="customVaccineDose" min="1" value="1"></div>
            </div>
            <div class="modal-actions">
              <button id="confirmAddCustomVaccine">Add</button>
              <button id="cancelCustomVaccine">Cancel</button>
            </div>
          </div>
        </div>
      </div>

      <div id="milestones" class="tab-pane">
        <div class="post-form">
          <textarea id="milestoneText" placeholder="Write milestone"></textarea>
          <input type="file" id="milestonePhoto" accept="image/*" multiple class="hidden-file">
          <div id="photoPreview" class="post-gallery preview"></div>
          <div class="composer-bar">
            <button id="attachPhoto" class="icon-button" title="Upload photos">📷</button>
            <span id="photoCount" class="composer-info">0/5</span>
            <span class="flex-spacer"></span>
            <button id="postMilestone">Post</button>
          </div>
        </div>
        <div id="feed" class="feed"></div>
        <div id="imageViewer" class="image-viewer hidden">
          <div class="image-viewer-content">
            <button id="closeViewer" class="close-viewer">×</button>
            <img id="viewerImage" src="" alt="">
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
</div>

<script>
  var viewOnly=true;
    var tabs=document.querySelectorAll('.tab-btn');
  tabs.forEach(function(b){b.addEventListener('click',function(){tabs.forEach(function(x){x.classList.remove('active')});document.querySelectorAll('.tab-pane').forEach(function(p){p.classList.remove('active')});b.classList.add('active');document.getElementById(b.dataset.tab).classList.add('active')})});
    function setVisitDateMin(){var today=new Date();var yyyy=today.getFullYear();var mm=('0'+(today.getMonth()+1)).slice(-2);var dd=('0'+today.getDate()).slice(-2);var min=yyyy+'-'+mm+'-'+dd;var grid=document.querySelector('#recordsBody .form-grid');if(!grid)return;var dateInputs=[].slice.call(grid.querySelectorAll('input[type="date"]'));if(dateInputs.length){dateInputs.forEach(function(inp){inp.min=min;inp.addEventListener('change',function(){if(this.value && this.value<min){this.value=min}})});var visitInput=dateInputs[0];var nextInput=dateInputs[dateInputs.length-1];if(visitInput&&nextInput){visitInput.addEventListener('change',function(){var v=this.value||min;nextInput.min=v;if(nextInput.value && nextInput.value<v){nextInput.value=v}})}}}
  document.getElementById('saveRecords').addEventListener('click',function(){var grid=document.querySelector('#recordsBody .form-grid');var inputs=grid.querySelectorAll('input');var payload={visit:inputs[0].value,age:inputs[1].value,weight:inputs[2].value,head:inputs[3].value,chest:inputs[4].value,length:inputs[5].value,instructions:inputs[6].value,next:inputs[7].value};fetch('records_api.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}).then(function(r){return r.json()}).then(function(){grid.querySelectorAll('input').forEach(function(inp){var s=document.createElement('span');s.className='saved-value';s.textContent=inp.value;inp.parentNode.innerHTML='';inp.parentNode.appendChild(s)})}).catch(function(e){console.error(e)})});
  var vaccineDoseMap={"BCG":1,"Hepatitis":4,"DPT":3,"DPT Booster":2,"OPV/IPV":3,"OPV/IPV Booster":2,"H. Influenzae B":4,"Rotavirus":1,"Measles":1,"MMR":2,"MMR Booster":2,"Pneumococcal Polysaccharide (PPV)":2,"Influenza":4,"Varicella":2,"Hepatitis A":2,"HPV":3,"Mantoux Test":1,"Typhoid":2};
  function getSavedImmunization(){try{return JSON.parse(localStorage.getItem('immunization')||'[]')}catch(e){return []}}
  function maxDoseForType(type){var max=0;getSavedImmunization().forEach(function(r){if(r.type===type){var d=parseInt(r.dose||0,10);if(d>max)max=d}});document.querySelectorAll('#immunizationBody .form-grid').forEach(function(g){var sel=g.querySelector('.vaccine-type');var oth=g.querySelector('.vaccine-type-other');var eff=(sel&&sel.value==='Others')?(oth?oth.value:''):sel?sel.value:'';if(eff===type){var d=parseInt((g.querySelector('.dose')||{}).value||0,10);if(d>max)max=d}});return max}
  function computeNextDose(type){var next=maxDoseForType(type)+1;var cap=vaccineDoseMap[type];if(cap){next=Math.min(next,cap)}return next||1}
  function attachVaccineTypeHandlers(root){var select=root.querySelector('.vaccine-type');var dose=root.querySelector('.dose');var other=root.querySelector('.vaccine-type-other');function updateDose(){var v=select.value;if(v==='Others'){other.style.display='block';var eff=other.value.trim();dose.value=eff?computeNextDose(eff):1}else{other.style.display='none';dose.value=computeNextDose(v)}}select.addEventListener('change',updateDose);if(other){other.addEventListener('input',updateDose)}updateDose()}
  function addCustomVaccine(name,count){var acc=document.getElementById('immunizationAccordion');var idx=acc.querySelectorAll('.acc-item').length;var shade=(idx%2===0)?'pink':'purple';var item=document.createElement('div');item.className='acc-item';item.dataset.type=name;var header=document.createElement('div');header.className='acc-header '+shade;header.innerHTML='<span class="acc-title">'+name+'</span><span class="acc-caret">▾</span>';var body=document.createElement('div');body.className='acc-body';var tbl=document.createElement('table');tbl.className='dose-table';tbl.innerHTML='<thead><tr><th>Dose</th><th>Location</th><th>Date</th><th>Reaction</th><th>Remarks</th></tr></thead><tbody></tbody>';var tb=tbl.querySelector('tbody');for(var i=1;i<=count;i++){var tr=document.createElement('tr');tr.className='dose-row';tr.dataset.type=name;tr.dataset.dose=i;var tdDose=document.createElement('td');tdDose.textContent=i;var tdLoc=document.createElement('td');tdLoc.innerHTML='<input type="text" placeholder="Site/Clinic">';var tdDate=document.createElement('td');tdDate.innerHTML='<input type="date">';var tdReact=document.createElement('td');tdReact.innerHTML='<input type="text" placeholder="Reaction">';var tdRemarks=document.createElement('td');tdRemarks.innerHTML='<input type="text" placeholder="Remarks">';tr.appendChild(tdDose);tr.appendChild(tdLoc);tr.appendChild(tdDate);tr.appendChild(tdReact);tr.appendChild(tdRemarks);tb.appendChild(tr)}body.appendChild(tbl);item.appendChild(header);item.appendChild(body);acc.appendChild(item)}
  function buildImmunizationAccordion(){var acc=document.getElementById('immunizationAccordion');acc.innerHTML='';Object.keys(vaccineDoseMap).forEach(function(type,idx){var count=vaccineDoseMap[type];var shade=(idx%2===0)?'pink':'purple';var item=document.createElement('div');item.className='acc-item';item.dataset.type=type;var header=document.createElement('div');header.className='acc-header '+shade;header.innerHTML='<span class="acc-title">'+type+'</span><span class="acc-caret">▾</span>';var body=document.createElement('div');body.className='acc-body';var tbl=document.createElement('table');tbl.className='dose-table';tbl.innerHTML='<thead><tr><th>Dose</th><th>Location</th><th>Date</th><th>Reaction</th><th>Remarks</th></tr></thead><tbody></tbody>';var tb=tbl.querySelector('tbody');for(var i=1;i<=count;i++){var tr=document.createElement('tr');tr.className='dose-row';tr.dataset.type=type;tr.dataset.dose=i;var tdDose=document.createElement('td');tdDose.textContent=i;var tdLoc=document.createElement('td');tdLoc.innerHTML='<input type="text" placeholder="Site/Clinic">';var tdDate=document.createElement('td');tdDate.innerHTML='<input type="date">';var tdReact=document.createElement('td');tdReact.innerHTML='<input type="text" placeholder="Reaction">';var tdRemarks=document.createElement('td');tdRemarks.innerHTML='<input type="text" placeholder="Remarks">';tr.appendChild(tdDose);tr.appendChild(tdLoc);tr.appendChild(tdDate);tr.appendChild(tdReact);tr.appendChild(tdRemarks);tb.appendChild(tr)}body.appendChild(tbl);item.appendChild(header);item.appendChild(body);acc.appendChild(item)});acc.addEventListener('click',function(e){var h=e.target.closest('.acc-header');if(!h)return;var it=h.parentNode;acc.querySelectorAll('.acc-item').forEach(function(x){if(x!==it)x.classList.remove('open')});it.classList.toggle('open')});var first=acc.querySelector('.acc-item');if(first)first.classList.add('open')}
  function setImmunizationDateMin(){var today=new Date();var yyyy=today.getFullYear();var mm=('0'+(today.getMonth()+1)).slice(-2);var dd=('0'+today.getDate()).slice(-2);var min=yyyy+'-'+mm+'-'+dd;var acc=document.getElementById('immunizationAccordion');if(!acc)return;acc.querySelectorAll('input[type="date"]').forEach(function(inp){inp.min=min;inp.addEventListener('change',function(){if(this.value && this.value<min){this.value=min}})})}
  function applySavedImmunization(){var acc=document.getElementById('immunizationAccordion');var saved=getSavedImmunization();if(!saved.length)return;var map={};saved.forEach(function(r){map[(r.type||'')+'|'+(r.dose||'')]=r});acc.querySelectorAll('.dose-row').forEach(function(r){var type=r.dataset.type;var dose=r.dataset.dose;var data=map[(type||'')+'|'+dose];if(!data)return;var cells=r.querySelectorAll('td');function setCell(cell,val){if(val==null)return;var inp=cell.querySelector('input');if(inp){inp.value=val}else{cell.innerHTML='';var span=document.createElement('span');span.className='saved-value';span.textContent=val;cell.appendChild(span)}}setCell(cells[1],data.location);setCell(cells[2],data.date);setCell(cells[3],data.reaction);setCell(cells[4],data.remarks)})}
  var customModal=document.getElementById('customVaccineModal');
    document.getElementById('openCustomModal').addEventListener('click',function(){customModal.classList.remove('hidden')});
    document.getElementById('cancelCustomVaccine').addEventListener('click',function(){customModal.classList.add('hidden')});
    document.getElementById('confirmAddCustomVaccine').addEventListener('click',function(){var name=document.getElementById('customVaccineName').value.trim();var count=parseInt(document.getElementById('customVaccineDose').value,10)||0;if(!name||count<1){customModal.classList.add('hidden');return}addCustomVaccine(name,count);setImmunizationDateMin();customModal.classList.add('hidden');document.getElementById('customVaccineName').value='';document.getElementById('customVaccineDose').value='1'});
    document.getElementById('saveImmunization').addEventListener('click',function(){
      var rows=[];
      var acc=document.getElementById('immunizationAccordion');
      acc.querySelectorAll('.dose-row').forEach(function(r){
        var cells=r.querySelectorAll('td');
        function getCellValue(cell){
          var inp=cell.querySelector('input');
          if(inp){return inp.value}else{return cell.textContent.trim()}
        }
        rows.push({
          type:r.dataset.type,
          dose:cells[0].textContent.trim(),
          location:getCellValue(cells[1]),
          date:getCellValue(cells[2]),
          reaction:getCellValue(cells[3]),
          remarks:getCellValue(cells[4])
        })
      });
      localStorage.setItem('immunization',JSON.stringify(rows))
    });
function renderImmunizationResult(){var container=document.getElementById('immunizationResult');if(!container)return;container.innerHTML='';var rows=getSavedImmunization();if(rows.length===0){var empty=document.createElement('div');empty.className='empty-note';empty.textContent='No immunizations saved yet.';container.appendChild(empty);return}rows.forEach(function(r){var max=vaccineDoseMap[r.type];var d=parseInt(r.dose||1,10);var doseText=max?('Dose '+d+' of '+max):('Dose '+d);var card=document.createElement('div');card.className='result-card';card.innerHTML='<div><span class="mini-label">Type</span><div class="mini-value">'+(r.type||'')+'</div></div><div><span class="mini-label">Dose</span><div class="mini-value"><span class="dose-badge">'+doseText+'</span></div></div><div><span class="mini-label">Location</span><div class="mini-value">'+(r.location||'')+'</div></div><div><span class="mini-label">Date</span><div class="mini-value">'+(r.date||'')+'</div></div><div><span class="mini-label">Reaction</span><div class="mini-value">'+(r.reaction||'')+'</div></div><div><span class="mini-label">Remarks</span><div class="mini-value">'+(r.remarks||'')+'</div></div>';container.appendChild(card)})}
window.addEventListener('load',function(){buildImmunizationAccordion();applySavedImmunization();setImmunizationDateMin();setVisitDateMin()});
window.addEventListener('load',renderImmunizationResult);
function formatRelative(iso){var d=new Date(iso);var now=new Date();var diff=Math.floor((now-d)/1000);if(diff<60)return 'just now';if(diff<3600)return Math.floor(diff/60)+' min ago';if(diff<86400)return Math.floor(diff/3600)+' hr ago';var days=Math.floor(diff/86400);if(days===1)return 'Yesterday';return days+' days ago'}
function renderMilestones(){var feed=document.getElementById('feed');var list=JSON.parse(localStorage.getItem('milestones')||'[]');feed.innerHTML='';list.forEach(function(m){var gallery='';if(m.photos&&m.photos.length){gallery='<div class="post-gallery">'+m.photos.map(function(u){return '<img class="post-thumb" src="'+u+'" data-src="'+u+'">'}).join('')+'</div>'}var card=document.createElement('div');card.className='post';card.innerHTML='<div class="post-meta">'+formatRelative(m.ts)+'</div><div class="post-text">'+(m.text||'')+'</div>'+gallery;feed.appendChild(card)})}
function readFilesAsDataUrls(files){return Promise.all(Array.from(files).slice(0,5).map(function(file){return new Promise(function(res){var fr=new FileReader();fr.onload=function(e){res(e.target.result)};fr.readAsDataURL(file)})}))}
function updatePostEnabled(){var t=document.getElementById('milestoneText').value.trim();var files=document.getElementById('milestonePhoto').files;var hasPhotos=files&&files.length>0;document.getElementById('postMilestone').disabled=!(t||hasPhotos)}
if(!viewOnly){
document.getElementById('attachPhoto').addEventListener('click',function(){document.getElementById('milestonePhoto').click()});
document.getElementById('milestonePhoto').addEventListener('change',function(){var n=Math.min(this.files.length,5);document.getElementById('photoCount').textContent=n+'/5';var preview=document.getElementById('photoPreview');readFilesAsDataUrls(this.files).then(function(urls){preview.innerHTML=urls.map(function(u){return '<img class="post-thumb" src="'+u+'" data-src="'+u+'">'}).join('')});updatePostEnabled()});
document.getElementById('milestoneText').addEventListener('input',updatePostEnabled);
document.getElementById('postMilestone').addEventListener('click',function(){var t=document.getElementById('milestoneText').value;var input=document.getElementById('milestonePhoto');var now=new Date();readFilesAsDataUrls(input.files).then(function(urls){var gallery='';if(urls.length){gallery='<div class="post-gallery">'+urls.map(function(u){return '<img class="post-thumb" src="'+u+'" data-src="'+u+'">'}).join('')+'</div>'}var card=document.createElement('div');card.className='post';card.innerHTML='<div class="post-meta">'+formatRelative(now.toISOString())+'</div><div class="post-text">'+t+'</div>'+gallery;document.getElementById('feed').prepend(card);var list=JSON.parse(localStorage.getItem('milestones')||'[]');list.unshift({ts:now.toISOString(),text:t,photos:urls});localStorage.setItem('milestones',JSON.stringify(list));document.getElementById('milestoneText').value='';input.value='';document.getElementById('photoCount').textContent='0/5';document.getElementById('photoPreview').innerHTML=''})});
window.addEventListener('load',updatePostEnabled);
} else {
window.addEventListener('load',function(){var pf=document.querySelector('.post-form');if(pf){pf.classList.add('readonly')}});
}
var viewer=document.getElementById('imageViewer');var viewerImg=document.getElementById('viewerImage');document.getElementById('closeViewer').addEventListener('click',function(){viewer.classList.add('hidden');viewerImg.src=''});viewer.addEventListener('click',function(e){if(e.target===viewer){viewer.classList.add('hidden');viewerImg.src=''}});document.getElementById('feed').addEventListener('click',function(e){var t=e.target;if(t.classList.contains('post-thumb')){viewerImg.src=t.dataset.src||t.src;viewer.classList.remove('hidden')}});
window.addEventListener('load',renderMilestones);

var sidebarToggle=document.getElementById('sidebarToggle');
var sidebar=document.getElementById('sidebar');
var container=document.querySelector('.container');
sidebarToggle.addEventListener('click',function(){sidebar.classList.toggle('collapsed')});
document.getElementById('backBtn').addEventListener('click',function(){history.back()});

var editProfileBtn=document.getElementById('editProfileBtn');
var editActions=document.getElementById('editActions');
var profileGrid=document.getElementById('profileGrid');
var nameEdit=document.getElementById('nameEditActions');
var photoEdit=document.getElementById('photoEditActions');
var changePhotoBtn=document.getElementById('changePhoto');
function toInputs(){profileGrid.querySelectorAll('.row').forEach(function(r){var key=r.dataset.field;var v=r.querySelector('.value');var current=v.textContent||'';var input;if(key==='dob'){input=document.createElement('input');input.type='date'}else if(key==='tob'){input=document.createElement('input');input.type='time'}else if(key==='gender'){input=document.createElement('select');var optM=document.createElement('option');optM.value='Male';optM.textContent='Male';var optF=document.createElement('option');optF.value='Female';optF.textContent='Female';input.appendChild(optM);input.appendChild(optF)}else{input=document.createElement('input');input.type='text';input.placeholder=r.querySelector('.label').textContent}input.value=current;input.dataset.key=key;if(key!=='gender'){input.style.width='100%';input.style.padding='6px 8px';input.style.border='1px solid #ccc';input.style.borderRadius='6px';input.style.fontSize='14px'}else{input.style.width='100%';input.style.padding='6px 8px';input.style.border='1px solid #ccc';input.style.borderRadius='6px';input.style.fontSize='14px'}v.replaceWith(input)});editActions.style.display='flex';nameEdit.style.display='flex';babyNameInput.value=babyNameEl.textContent.trim();photoEdit.style.display='flex'}
function toSpans(){profileGrid.querySelectorAll('[data-key]').forEach(function(inp){var span=document.createElement('span');span.className='value';span.textContent=inp.value;inp.replaceWith(span)});editActions.style.display='none';nameEdit.style.display='none';photoEdit.style.display='none'}
document.getElementById('editProfileBtn').addEventListener('click',toInputs);
document.getElementById('cancelEdit').addEventListener('click',toSpans);
document.getElementById('saveProfile').addEventListener('click',function(){var data=JSON.parse(localStorage.getItem('profile')||'{}');profileGrid.querySelectorAll('[data-key]').forEach(function(i){data[i.dataset.key]=i.value});data.child_name=babyNameInput.value.trim();localStorage.setItem('profile',JSON.stringify(data));babyNameEl.textContent=data.child_name||'Child Name';toSpans()});

window.addEventListener('load',function(){
  var data=JSON.parse(localStorage.getItem('profile')||'{}');
  profileGrid.querySelectorAll('.row').forEach(function(r){var key=r.dataset.field;var span=r.querySelector('.value');if(data[key]){span.textContent=data[key]}});
  if(data.child_name){document.getElementById('babyName').textContent=data.child_name}
  if(!sidebar.classList.contains('collapsed')){container.classList.add('with-sidebar')}
});
sidebarToggle.addEventListener('click',function(){
  var open=!sidebar.classList.contains('collapsed');
  if(open){container.classList.add('with-sidebar')} else {container.classList.remove('with-sidebar')}
});

var avatar=document.getElementById('avatar');
var avatarInput=document.getElementById('avatarInput');
avatar.addEventListener('click',function(){avatarInput.click()});
changePhotoBtn.addEventListener('click',function(){avatarInput.click()});
avatarInput.addEventListener('change',function(){var f=this.files[0];if(!f)return;var fr=new FileReader();fr.onload=function(e){var url=e.target.result;avatar.style.backgroundImage='url('+url+')';avatar.classList.add('has-photo');var data=JSON.parse(localStorage.getItem('profile')||'{}');data.avatar=url;localStorage.setItem('profile',JSON.stringify(data))};fr.readAsDataURL(f)});
window.addEventListener('load',function(){var data=JSON.parse(localStorage.getItem('profile')||'{}');if(data.avatar){avatar.style.backgroundImage='url('+data.avatar+')';avatar.classList.add('has-photo')}});

var babyNameEl=document.getElementById('babyName');
var nameEdit=document.getElementById('nameEditActions');
var babyNameInput=document.getElementById('babyNameInput');
babyNameEl.addEventListener('click',function(){babyNameInput.value=babyNameEl.textContent.trim();nameEdit.style.display='flex'});

document.getElementById('recordSearchDate').addEventListener('change',function(){var d=this.value;if(!d)return;fetch('records_api.php?date='+encodeURIComponent(d)).then(function(r){return r.json()}).then(function(list){var body=document.getElementById('recordsBody');body.innerHTML='';if(!list.length){var empty=document.createElement('div');empty.className='empty-note';empty.textContent='No records for selected date.';body.appendChild(empty);return}list.forEach(function(row){var grid=document.createElement('div');grid.className='form-grid';function add(label,value,wide){var field=document.createElement('div');field.className='field'+(wide?' wide':'');var lab=document.createElement('span');lab.className='field-label';lab.textContent=label;var val=document.createElement('span');val.className='saved-value';val.textContent=value||'';field.appendChild(lab);field.appendChild(val);grid.appendChild(field)}add('Visit date',row.visit);add('Age',row.age);add('Weight',row.weight);add('Head circumference',row.head);add('Chest circumference',row.chest);add('Length',row.length);add("Doctor's instructions",row.instructions,true);add('Date of next visit',row.next);body.appendChild(grid)})}).catch(function(e){console.error(e)})});
</script>
</body>
</html>
