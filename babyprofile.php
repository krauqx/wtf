<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userId = $_SESSION['user_id'] ?? '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>JAM Lying-In Clinic - My Child's Record</title>
<link rel="stylesheet" href="babyprofile.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="container">
  <div class="topbar">
    <div class="title">JAM Lying-In Clinic - My Child's Record</div>
    <div class="topbar-actions">
      <button type="button" class="topbar-btn secondary" onclick="window.location.href='pdash.php'">← Back</button>
      <button type="button" class="topbar-btn primary" onclick="enableProfileEdit()">✎ Edit Profile</button>
    </div>
  </div>
  <div class="columns one">
    <div class="col main">
      <div class="profile-card horizontal">
        <div class="profile-left">
          <div class="avatar circle" id="avatar"></div>
          <input type="file" id="avatarInput" accept="image/*" class="hidden-file">
          <div class="baby-name" id="babyName">Name</div>
          <div class="name-edit-actions" id="nameEditActions">
            <input type="text" id="babyNameInput" placeholder="Name">
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
            <button type="button" id="saveProfile" class="primary" onclick="saveProfileNow()">Save Profile</button>
            <button type="button" id="cancelEdit" class="secondary" onclick="cancelEditNow()">Cancel</button>
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
            <div class="field"><span class="field-label">Visit date</span><span class="saved-value"></span></div>
            <div class="field"><span class="field-label">Age</span><span class="saved-value"></span></div>
            <div class="field"><span class="field-label">Weight</span><span class="saved-value"></span></div>
            <div class="field"><span class="field-label">Head circumference</span><span class="saved-value"></span></div>
            <div class="field"><span class="field-label">Chest circumference</span><span class="saved-value"></span></div>
            <div class="field"><span class="field-label">Length</span><span class="saved-value"></span></div>
            <div class="field wide"><span class="field-label">Doctor's instructions</span><span class="saved-value"></span></div>
            <div class="field"><span class="field-label">Date of next visit</span><span class="saved-value"></span></div>
          </div>
        </div>
        
      </div>

      <div id="immunization" class="tab-pane">
        <div class="accordion" id="immunizationAccordion"></div>
      </div>

      <div id="milestones" class="tab-pane">
        <div class="post-form">
          <textarea id="milestoneText" placeholder="Write milestone" style="font-family: 'Inter', sans-serif;"></textarea>
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
  const currentUserId = '<?= htmlspecialchars($userId, ENT_QUOTES) ?>';
  const babyGalleryKey = 'babyGallery_' + currentUserId;
  const profileKey = 'profile_' + currentUserId;

  function enableProfileEdit() {
    console.log('enableProfileEdit called');

    var profileGrid = document.getElementById('profileGrid');
    var editActions = document.getElementById('editActions');
    var nameEditActions = document.getElementById('nameEditActions');
    var photoEditActions = document.getElementById('photoEditActions');
    var babyNameInput = document.getElementById('babyNameInput');
    var babyName = document.getElementById('babyName');

    if (!profileGrid) {
      alert('profileGrid not found');
      return;
    }

    profileGrid.querySelectorAll('.row').forEach(function(r) {
      var key = r.getAttribute('data-field');
      var valueEl = r.querySelector('.value');

      if (!valueEl) return;
      if (r.querySelector('input[data-key]')) return;

      var current = valueEl.textContent || '';
      var input = document.createElement('input');
      input.type = (key === 'dob') ? 'date' : (key === 'tob' ? 'time' : 'text');
      input.value = current;
      input.setAttribute('data-key', key);

      input.style.width = '100%';
      input.style.padding = '8px';
      input.style.border = '1px solid #ccc';
      input.style.borderRadius = '6px';
      input.style.display = 'block';

      valueEl.replaceWith(input);
    });

    if (editActions) editActions.style.display = 'flex';
    if (nameEditActions) nameEditActions.style.display = 'block';
    if (photoEditActions) photoEditActions.style.display = 'block';

    if (babyName && babyNameInput) {
      babyNameInput.value = babyName.textContent || '';
      babyNameInput.style.display = 'block';
    }

    alert('Edit mode opened');
  }

  function saveProfileNow() {
    var profileGrid = document.getElementById('profileGrid');
    var editActions = document.getElementById('editActions');
    var nameEditActions = document.getElementById('nameEditActions');
    var photoEditActions = document.getElementById('photoEditActions');
    var babyNameInput = document.getElementById('babyNameInput');
    var babyName = document.getElementById('babyName');

    if (!profileGrid) return;

    var data = {};
    try {
      data = JSON.parse(localStorage.getItem(profileKey) || '{}');
    } catch (e) {
      data = {};
    }

    profileGrid.querySelectorAll('input[data-key]').forEach(function(inp) {
      var key = inp.getAttribute('data-key');
      data[key] = inp.value;
    });

    if (babyNameInput) {
      data.child_name = babyNameInput.value.trim();
      if (babyName) {
        babyName.textContent = data.child_name || 'Name';
      }
    }

    localStorage.setItem(profileKey, JSON.stringify(data));

    profileGrid.querySelectorAll('input[data-key]').forEach(function(inp) {
      var span = document.createElement('span');
      span.className = 'value';
      span.textContent = inp.value;
      inp.replaceWith(span);
    });

    if (editActions) editActions.style.display = 'none';
    if (nameEditActions) nameEditActions.style.display = 'none';
    if (photoEditActions) photoEditActions.style.display = 'none';
  }

  function cancelEditNow() {
    var profileGrid = document.getElementById('profileGrid');
    var editActions = document.getElementById('editActions');
    var nameEditActions = document.getElementById('nameEditActions');
    var photoEditActions = document.getElementById('photoEditActions');
    var babyName = document.getElementById('babyName');

    if (!profileGrid) return;

    var data = {};
    try {
      data = JSON.parse(localStorage.getItem(profileKey) || '{}');
    } catch (e) {
      data = {};
    }

    profileGrid.querySelectorAll('input[data-key]').forEach(function(inp) {
      var key = inp.getAttribute('data-key');
      var span = document.createElement('span');
      span.className = 'value';
      span.textContent = data[key] || '';
      inp.replaceWith(span);
    });

    if (babyName && data.child_name) {
      babyName.textContent = data.child_name;
    }

    if (editActions) editActions.style.display = 'none';
    if (nameEditActions) nameEditActions.style.display = 'none';
    if (photoEditActions) photoEditActions.style.display = 'none';
  }

  function setVisitDateMin() {
    var today = new Date();
    var yyyy = today.getFullYear();
    var mm = ('0' + (today.getMonth() + 1)).slice(-2);
    var dd = ('0' + today.getDate()).slice(-2);
    var min = yyyy + '-' + mm + '-' + dd;
    var grid = document.querySelector('#recordsBody .form-grid');
    if (!grid) return;
    var dateInputs = [].slice.call(grid.querySelectorAll('input[type="date"]'));
    if (dateInputs.length) {
      dateInputs.forEach(function(inp) {
        inp.min = min;
        inp.addEventListener('change', function() {
          if (this.value && this.value < min) this.value = min;
        });
      });
      var visitInput = dateInputs[0];
      var nextInput = dateInputs[dateInputs.length - 1];
      if (visitInput && nextInput) {
        visitInput.addEventListener('change', function() {
          var v = this.value || min;
          nextInput.min = v;
          if (nextInput.value && nextInput.value < v) nextInput.value = v;
        });
      }
    }
  }

  var vaccineDoseMap = {
    "BCG": 1,
    "Hepatitis": 4,
    "DPT": 3,
    "DPT Booster": 2,
    "OPV/IPV": 3,
    "OPV/IPV Booster": 2,
    "H. Influenzae B": 4,
    "Rotavirus": 1,
    "Measles": 1,
    "MMR": 2,
    "MMR Booster": 2,
    "Pneumococcal Polysaccharide (PPV)": 2,
    "Influenza": 4,
    "Varicella": 2,
    "Hepatitis A": 2,
    "HPV": 3,
    "Mantoux Test": 1,
    "Typhoid": 2
  };

  function getSavedImmunization() {
    try { return JSON.parse(localStorage.getItem('immunization') || '[]'); }
    catch (e) { return []; }
  }

  function maxDoseForType(type) {
    var max = 0;
    getSavedImmunization().forEach(function(r) {
      if (r.type === type) {
        var d = parseInt(r.dose || 0, 10);
        if (d > max) max = d;
      }
    });
    return max;
  }

  function computeNextDose(type) {
    var next = maxDoseForType(type) + 1;
    var cap = vaccineDoseMap[type];
    if (cap) next = Math.min(next, cap);
    return next || 1;
  }

  function buildImmunizationAccordion() {
    var acc = document.getElementById('immunizationAccordion');
    if (!acc) return;
    acc.innerHTML = '';

    Object.keys(vaccineDoseMap).forEach(function(type, idx) {
      var count = vaccineDoseMap[type];
      var shade = (idx % 2 === 0) ? 'pink' : 'purple';
      var item = document.createElement('div');
      item.className = 'acc-item';
      item.dataset.type = type;

      var header = document.createElement('div');
      header.className = 'acc-header ' + shade;
      header.innerHTML = '<span class="acc-title">' + type + '</span><span class="acc-caret">▾</span>';

      var body = document.createElement('div');
      body.className = 'acc-body';

      var tbl = document.createElement('table');
      tbl.className = 'dose-table';
      tbl.innerHTML = '<thead><tr><th>Dose</th><th>Location</th><th>Date</th><th>Reaction</th><th>Remarks</th></tr></thead><tbody></tbody>';

      var tb = tbl.querySelector('tbody');
      for (var i = 1; i <= count; i++) {
        var tr = document.createElement('tr');
        tr.className = 'dose-row';
        tr.dataset.type = type;
        tr.dataset.dose = i;
        tr.innerHTML = '<td>' + i + '</td><td></td><td></td><td></td><td></td>';
        tb.appendChild(tr);
      }

      body.appendChild(tbl);
      item.appendChild(header);
      item.appendChild(body);
      acc.appendChild(item);
    });

    acc.addEventListener('click', function(e) {
      var h = e.target.closest('.acc-header');
      if (!h) return;
      var it = h.parentNode;
      acc.querySelectorAll('.acc-item').forEach(function(x) {
        if (x !== it) x.classList.remove('open');
      });
      it.classList.toggle('open');
    });

    var first = acc.querySelector('.acc-item');
    if (first) first.classList.add('open');
  }

  function applySavedImmunization() {
    var acc = document.getElementById('immunizationAccordion');
    if (!acc) return;
    var saved = getSavedImmunization();
    if (!saved.length) return;

    var map = {};
    saved.forEach(function(r) {
      map[(r.type || '') + '|' + (r.dose || '')] = r;
    });

    acc.querySelectorAll('.dose-row').forEach(function(r) {
      var type = r.dataset.type;
      var dose = r.dataset.dose;
      var data = map[(type || '') + '|' + dose];
      if (!data) return;

      var cells = r.querySelectorAll('td');
      if (cells[1]) cells[1].textContent = data.location || '';
      if (cells[2]) cells[2].textContent = data.date || '';
      if (cells[3]) cells[3].textContent = data.reaction || '';
      if (cells[4]) cells[4].textContent = data.remarks || '';
    });
  }

  function renderImmunizationResult() {
    var container = document.getElementById('immunizationResult');
    if (!container) return;

    container.innerHTML = '';
    var rows = getSavedImmunization();

    if (rows.length === 0) {
      var empty = document.createElement('div');
      empty.className = 'empty-note';
      empty.textContent = 'No immunizations saved yet.';
      container.appendChild(empty);
      return;
    }

    rows.forEach(function(r) {
      var max = vaccineDoseMap[r.type];
      var d = parseInt(r.dose || 1, 10);
      var doseText = max ? ('Dose ' + d + ' of ' + max) : ('Dose ' + d);

      var card = document.createElement('div');
      card.className = 'result-card';
      card.innerHTML =
        '<div><span class="mini-label">Type</span><div class="mini-value">' + (r.type || '') + '</div></div>' +
        '<div><span class="mini-label">Dose</span><div class="mini-value"><span class="dose-badge">' + doseText + '</span></div></div>' +
        '<div><span class="mini-label">Location</span><div class="mini-value">' + (r.location || '') + '</div></div>' +
        '<div><span class="mini-label">Date</span><div class="mini-value">' + (r.date || '') + '</div></div>' +
        '<div><span class="mini-label">Reaction</span><div class="mini-value">' + (r.reaction || '') + '</div></div>' +
        '<div><span class="mini-label">Remarks</span><div class="mini-value">' + (r.remarks || '') + '</div></div>';

      container.appendChild(card);
    });
  }

  function formatRelative(iso) {
    var d = new Date(iso);
    var now = new Date();
    var diff = Math.floor((now - d) / 1000);
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
    if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
    var days = Math.floor(diff / 86400);
    if (days === 1) return 'Yesterday';
    return days + ' days ago';
  }

  function renderMilestones() {
    var feed = document.getElementById('feed');
    if (!feed) return;

    var list = JSON.parse(localStorage.getItem('milestones') || '[]');
    feed.innerHTML = '';

    list.forEach(function(m) {
      var gallery = '';
      if (m.photos && m.photos.length) {
        gallery = '<div class="post-gallery">' +
          m.photos.map(function(u) {
            return '<img class="post-thumb" src="' + u + '" data-src="' + u + '">';
          }).join('') + '</div>';
      }

      var card = document.createElement('div');
      card.className = 'post';
      card.dataset.id = m.ts;
      card.innerHTML =
        '<div class="post-header">' +
          '<div class="post-meta">' + formatRelative(m.ts) + '</div>' +
          '<button class="more-btn" aria-label="More">⋯</button>' +
          '<div class="post-menu">' +
            '<button class="menu-item edit">Edit</button>' +
            '<button class="menu-item delete">Delete</button>' +
          '</div>' +
        '</div>' +
        '<div class="post-text">' + (m.text || '') + '</div>' + gallery;

      feed.appendChild(card);
    });
  }

  function readFilesAsDataUrls(files) {
    return Promise.all(Array.from(files).map(function(file) {
      return new Promise(function(res) {
        var fr = new FileReader();
        fr.onload = function(e) { res(e.target.result); };
        fr.readAsDataURL(file);
      });
    }));
  }

  function updatePostEnabled() {
    var textEl = document.getElementById('milestoneText');
    var photoEl = document.getElementById('milestonePhoto');
    var postBtn = document.getElementById('postMilestone');
    if (!textEl || !photoEl || !postBtn) return;

    var t = textEl.value.trim();
    var files = photoEl.files;
    var hasPhotos = files && files.length > 0;
    postBtn.disabled = !(t || hasPhotos);
  }

  var selectedFiles = [];

  function updatePreview() {
    var preview = document.getElementById('photoPreview');
    var count = document.getElementById('photoCount');
    if (!preview || !count) return;

    count.textContent = selectedFiles.length + '/5';

    readFilesAsDataUrls(selectedFiles).then(function(urls) {
      preview.innerHTML = urls.map(function(u, index) {
        return '<div class="preview-item">' +
               '<img class="preview-thumb" src="' + u + '">' +
               '<button class="delete-photo-btn" data-index="' + index + '">×</button>' +
               '</div>';
      }).join('');

      preview.querySelectorAll('.delete-photo-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
          var idx = parseInt(this.dataset.index, 10);
          selectedFiles.splice(idx, 1);
          updatePreview();
          updatePostEnabled();
        });
      });
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    buildImmunizationAccordion();
    applySavedImmunization();
    setVisitDateMin();
    renderImmunizationResult();
    renderMilestones();
    updatePostEnabled();

    var tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(function(b) {
      b.addEventListener('click', function() {
        tabs.forEach(function(x) { x.classList.remove('active'); });
        document.querySelectorAll('.tab-pane').forEach(function(p) { p.classList.remove('active'); });
        b.classList.add('active');
        document.getElementById(b.dataset.tab).classList.add('active');
      });
    });

    var data = JSON.parse(localStorage.getItem(profileKey) || '{}');
    profileGrid.querySelectorAll('.row').forEach(function(r) {
      var key = r.dataset.field;
      var span = r.querySelector('.value');
      if (data[key]) span.textContent = data[key];
    });

    if (data.child_name && babyName) babyName.textContent = data.child_name;

    var avatarSrc = data.avatar || '';
    if (!avatarSrc) {
      try {
        var gallery = JSON.parse(localStorage.getItem(babyGalleryKey) || '[]');
        if (Array.isArray(gallery) && gallery.length > 0) {
          avatarSrc = gallery[0].src || '';
        }
      } catch (e) {}
    }

    if (avatarSrc) {
      var avatar = document.getElementById('avatar');
      if (avatar) {
        avatar.style.backgroundImage = 'url(' + avatarSrc + ')';
        avatar.style.backgroundSize = 'cover';
        avatar.style.backgroundPosition = 'center';
      }
    }

    var changePhoto = document.getElementById('changePhoto');
    if (changePhoto) {
      changePhoto.addEventListener('click', function() {
        var avatarInput = document.getElementById('avatarInput');
        if (avatarInput) avatarInput.click();
      });
    }

    var avatarInput = document.getElementById('avatarInput');
    if (avatarInput) {
      avatarInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
          var reader = new FileReader();
          reader.onload = function(e) {
            var avatar = document.getElementById('avatar');
            if (avatar) {
              avatar.style.backgroundImage = 'url(' + e.target.result + ')';
              avatar.style.backgroundSize = 'cover';
              avatar.style.backgroundPosition = 'center';
              var data = JSON.parse(localStorage.getItem(profileKey) || '{}');
              data.avatar = e.target.result;
              localStorage.setItem(profileKey, JSON.stringify(data));
            }
          };
          reader.readAsDataURL(this.files[0]);
        }
      });
    }

    var attachPhoto = document.getElementById('attachPhoto');
    if (attachPhoto) {
      attachPhoto.addEventListener('click', function() {
        var milestonePhoto = document.getElementById('milestonePhoto');
        if (milestonePhoto) milestonePhoto.click();
      });
    }

    var milestonePhoto = document.getElementById('milestonePhoto');
    if (milestonePhoto) {
      milestonePhoto.addEventListener('change', function() {
        var files = Array.from(this.files);
        selectedFiles = selectedFiles.concat(files).slice(0, 5);
        updatePreview();
        updatePostEnabled();
      });
    }

    var milestoneText = document.getElementById('milestoneText');
    if (milestoneText) {
      milestoneText.addEventListener('input', updatePostEnabled);
    }

    var postMilestone = document.getElementById('postMilestone');
    if (postMilestone) {
      postMilestone.addEventListener('click', function() {
        var t = document.getElementById('milestoneText').value;
        var now = new Date();

        readFilesAsDataUrls(selectedFiles).then(function(urls) {
          var gallery = '';
          if (urls.length) {
            gallery = '<div class="post-gallery">' +
              urls.map(function(u) {
                return '<img class="post-thumb" src="' + u + '" data-src="' + u + '">';
              }).join('') + '</div>';
          }

          var card = document.createElement('div');
          card.className = 'post';
          card.dataset.id = now.toISOString();
          card.innerHTML =
            '<div class="post-header">' +
              '<div class="post-meta">' + formatRelative(now.toISOString()) + '</div>' +
              '<button class="more-btn" aria-label="More">⋯</button>' +
              '<div class="post-menu">' +
                '<button class="menu-item edit">Edit</button>' +
                '<button class="menu-item delete">Delete</button>' +
              '</div>' +
            '</div>' +
            '<div class="post-text">' + t + '</div>' + gallery;

          document.getElementById('feed').prepend(card);

          var list = JSON.parse(localStorage.getItem('milestones') || '[]');
          list.unshift({ ts: now.toISOString(), text: t, photos: urls });
          localStorage.setItem('milestones', JSON.stringify(list));

          document.getElementById('milestoneText').value = '';
          document.getElementById('milestonePhoto').value = '';
          selectedFiles = [];
          updatePreview();
          updatePostEnabled();
        });
      });
    }

    var closeViewer = document.getElementById('closeViewer');
    if (closeViewer) {
      closeViewer.addEventListener('click', function() {
        var imageViewer = document.getElementById('imageViewer');
        if (imageViewer) imageViewer.classList.add('hidden');
      });
    }

    var imageViewer = document.getElementById('imageViewer');
    if (imageViewer) {
      imageViewer.addEventListener('click', function(e) {
        if (e.target === this) this.classList.add('hidden');
      });
    }

    var photoPreview = document.getElementById('photoPreview');
    if (photoPreview) {
      photoPreview.addEventListener('click', function(e) {
        if (e.target.classList.contains('post-thumb')) {
          var viewerImage = document.getElementById('viewerImage');
          var imageViewer = document.getElementById('imageViewer');
          if (viewerImage && imageViewer) {
            viewerImage.src = e.target.dataset.src;
            imageViewer.classList.remove('hidden');
          }
        }
      });
    }

    var feed = document.getElementById('feed');
    if (feed) {
      feed.addEventListener('click', function(e) {
        if (e.target.classList.contains('post-thumb')) {
          var viewerImage = document.getElementById('viewerImage');
          var imageViewer = document.getElementById('imageViewer');
          if (viewerImage && imageViewer) {
            viewerImage.src = e.target.dataset.src;
            imageViewer.classList.remove('hidden');
          }
        }

        if (e.target.classList.contains('more-btn')) {
          var post = e.target.closest('.post');
          var menu = post.querySelector('.post-menu');
          if (menu) {
            document.querySelectorAll('.post-menu.open').forEach(function(m) {
              if (m !== menu) m.classList.remove('open');
            });
            menu.classList.toggle('open');
          }
        }

        if (e.target.classList.contains('menu-item')) {
          var post = e.target.closest('.post');
          var id = post && post.dataset.id;
          var list = JSON.parse(localStorage.getItem('milestones') || '[]');

          if (e.target.classList.contains('delete')) {
            list = list.filter(function(m) { return m.ts !== id; });
            localStorage.setItem('milestones', JSON.stringify(list));
            renderMilestones();
          } else if (e.target.classList.contains('edit')) {
            enterInlineEdit(post, id);
          }
        }
      });

      document.addEventListener('click', function(ev) {
        if (!ev.target.closest('.post')) {
          document.querySelectorAll('.post-menu.open').forEach(function(m) {
            m.classList.remove('open');
          });
        }
      });
    }

    function enterInlineEdit(post, id) {
      document.querySelectorAll('.post.editing').forEach(function(p) {
        if (p !== post) exitInlineEdit(p);
      });

      post.classList.add('editing');
      var textEl = post.querySelector('.post-text');
      var current = textEl ? textEl.textContent : '';

      var editor = document.createElement('div');
      editor.className = 'post-edit';
      editor.innerHTML =
        '<textarea class="post-edit-text">' + (current || '') + '</textarea>' +
        '<div class="post-edit-bar">' +
          '<button class="inline-primary save-inline">Save</button>' +
          '<button class="inline-secondary cancel-inline">Cancel</button>' +
        '</div>';

      textEl.replaceWith(editor);

      var saveBtn = editor.querySelector('.save-inline');
      var cancelBtn = editor.querySelector('.cancel-inline');

      saveBtn.addEventListener('click', function() {
        var val = editor.querySelector('.post-edit-text').value;
        var list = JSON.parse(localStorage.getItem('milestones') || '[]');
        list = list.map(function(m) {
          if (m.ts === id) m.text = val;
          return m;
        });
        localStorage.setItem('milestones', JSON.stringify(list));
        renderMilestones();
      });

      cancelBtn.addEventListener('click', function() {
        exitInlineEdit(post);
      });
    }

    function exitInlineEdit(post) {
      var editor = post.querySelector('.post-edit');
      if (!editor) return;

      var id = post.dataset.id;
      var list = JSON.parse(localStorage.getItem('milestones') || '[]');
      var original = '';

      for (var i = 0; i < list.length; i++) {
        if (list[i].ts === id) {
          original = list[i].text || '';
          break;
        }
      }

      var textEl = document.createElement('div');
      textEl.className = 'post-text';
      textEl.textContent = original;
      editor.replaceWith(textEl);
      post.classList.remove('editing');
    }

    var recordSearchDate = document.getElementById('recordSearchDate');
    if (recordSearchDate) {
      recordSearchDate.addEventListener('change', function() {
        var date = this.value;
        if (!date) return;

        fetch('records_api.php?date=' + encodeURIComponent(date))
          .then(function(response) { return response.json(); })
          .then(function(data) {
            var grid = document.querySelector('#recordsBody .form-grid');
            if (!grid) return;
            var savedValues = grid.querySelectorAll('.saved-value');
            savedValues.forEach(function(s) { s.textContent = ''; });

            var record = (Array.isArray(data) && data.length > 0) ? data[0] : null;
            if (record) {
              if (savedValues[0]) savedValues[0].textContent = record.visit || '';
              if (savedValues[1]) savedValues[1].textContent = record.age || '';
              if (savedValues[2]) savedValues[2].textContent = record.weight || '';
              if (savedValues[3]) savedValues[3].textContent = record.head || '';
              if (savedValues[4]) savedValues[4].textContent = record.chest || '';
              if (savedValues[5]) savedValues[5].textContent = record.length || '';
              if (savedValues[6]) savedValues[6].textContent = record.instructions || '';
              if (savedValues[7]) savedValues[7].textContent = record.next || '';
            }
          })
          .catch(function(err) {
            console.error('Error fetching records:', err);

            var records = JSON.parse(localStorage.getItem('records') || '[]');
            var found = records.find(function(r) { return r.visit === date; });
            var grid = document.querySelector('#recordsBody .form-grid');
            if (!grid) return;
            var savedValues = grid.querySelectorAll('.saved-value');
            savedValues.forEach(function(s) { s.textContent = ''; });

            if (found) {
              if (savedValues[0]) savedValues[0].textContent = found.visit || '';
              if (savedValues[1]) savedValues[1].textContent = found.age || '';
              if (savedValues[2]) savedValues[2].textContent = found.weight || '';
              if (savedValues[3]) savedValues[3].textContent = found.head || '';
              if (savedValues[4]) savedValues[4].textContent = found.chest || '';
              if (savedValues[5]) savedValues[5].textContent = found.length || '';
              if (savedValues[6]) savedValues[6].textContent = found.notes || found.instructions || '';
              if (savedValues[7]) savedValues[7].textContent = found.next || found.nextVisit || '';
            }
          });
      });
    }
  });
</script>
</body>
</html>
