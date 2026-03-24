<?php

session_start();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Management Panel</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="mwadmin.css">
    <!-- Typography & Palette: Hospital-themed system defined in mwadmin.css -->
</head>
<body class="has-mini-nav">
    <!-- Navigation Breadcrumb -->
    
    <nav class="mini-nav">
        <div class="brand">Admin</div>
        <a href="#" data-target="tab-dashboard"><span class="nav-icon">📊</span><span class="nav-label">Dashboard</span></a>
        <a href="#" data-target="tab-new-user"><span class="nav-icon">👤</span><span class="nav-label">New User</span></a>
        <a href="#" data-target="tab-doctors"><span class="nav-icon">🩺</span><span class="nav-label">Doctors</span></a>
        <a href="#" data-target="tab-services"><span class="nav-icon">💼</span><span class="nav-label">Services</span></a>
        <a href="dashboard.php" class="nav-back"><span class="nav-icon">←</span><span class="nav-label">Back to Dashboard</span></a>
        <div class="spacer"></div>
        <button class="mini-nav-toggle" id="miniNavToggle">⟷</button>
    </nav>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
      const nav = document.querySelector('.mini-nav');
      const btn = document.getElementById('miniNavToggle');
      if (nav && btn) {
        btn.addEventListener('click', function(){
          nav.classList.toggle('collapsed');
          document.body.classList.toggle('nav-collapsed');
        });
      }
      const sections = document.querySelectorAll('.tab-section');
      function showTab(id) {
        sections.forEach(s => s.style.display = 'none');
        const el = document.getElementById(id);
        if (el) el.style.display = '';
      }
      document.querySelectorAll('.mini-nav a[data-target]').forEach(a => {
        a.addEventListener('click', function(e){
          e.preventDefault();
          showTab(this.getAttribute('data-target'));
        });
      });
      showTab('tab-dashboard');
    });
    </script>
    
    <!-- Admin Header -->
    <section id="tab-new-user" class="tab-section" style="display:none;">
        <div class="section-header">
            <h2 class="section-title">Register New User</h2>
        </div>
        <div id="tab-content-new-user" class="section-grid"></div>
    </section>
    <section id="tab-dashboard" class="tab-section" style="display:none; margin:0 1.5rem 1.5rem;">
        <div class="admin-header">
          <div>
            <h1>Admin Management Panel</h1>
            <p>Manage users, schedules, and service configurations</p>
          </div>
        </div>
        <div class="admin-card">
          <div class="card-header">
            <h2 class="card-title">Recent Activities</h2>
          </div>
          <div class="form-actions" style="gap:8px; align-items:center;">
            <button id="activityFilterAll" class="btn btn-secondary">All</button>
            <button id="activityFilterStaff" class="btn btn-secondary">Staff</button>
            <button id="activityFilterDoctors" class="btn btn-secondary">Doctors</button>
            <button id="activityFilterUsers" class="btn btn-secondary">Users</button>
            <div style="margin-left:auto; display:flex; gap:8px;">
              <button id="activityRefreshBtn" class="btn btn-secondary">Refresh</button>
              <button id="activityMarkAllReadBtn" class="btn btn-secondary">Mark All Read</button>
            </div>
          </div>
          <div id="activityList" style="display:flex; flex-direction:column; gap:10px;">
            <div class="activity-item" data-type="staff" style="display:flex; align-items:center; justify-content:space-between; padding:12px; border:1px solid #e2e8f0; border-radius:12px;">
              <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-weight:600;">Staff</span>
                <span style="color:#64748b;">Updated appointment status</span>
              </div>
              <span style="color:#64748b;">10:25 AM</span>
            </div>
            <div class="activity-item" data-type="doctor" style="display:flex; align-items:center; justify-content:space-between; padding:12px; border:1px solid #e2e8f0; border-radius:12px;">
              <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-weight:600;">Doctor</span>
                <span style="color:#64748b;">Added new service to catalog</span>
              </div>
              <span style="color:#64748b;">09:42 AM</span>
            </div>
            <div class="activity-item" data-type="user" style="display:flex; align-items:center; justify-content:space-between; padding:12px; border:1px solid #e2e8f0; border-radius:12px;">
              <div style="display:flex; align-items:center; gap:10px;">
                <span style="font-weight:600;">User</span>
                <span style="color:#64748b;">Registered new account</span>
              </div>
              <span style="color:#64748b;">Yesterday</span>
            </div>
          </div>
        </div>
        <script>
          (function(){
            const list = document.getElementById('activityList');
            const items = list ? Array.from(list.querySelectorAll('.activity-item')) : [];
            function filter(type){
              items.forEach(it => { it.style.display = (!type || it.dataset.type === type) ? 'flex' : 'none'; });
            }
            const byId = id => document.getElementById(id);
            const all = byId('activityFilterAll');
            const stf = byId('activityFilterStaff');
            const doc = byId('activityFilterDoctors');
            const usr = byId('activityFilterUsers');
            const ref = byId('activityRefreshBtn');
            const mark = byId('activityMarkAllReadBtn');
            if (all) all.onclick = () => filter('');
            if (stf) stf.onclick = () => filter('staff');
            if (doc) doc.onclick = () => filter('doctor');
            if (usr) usr.onclick = () => filter('user');
            if (mark) mark.onclick = () => { items.forEach(it => { it.style.opacity = '0.7'; }); };
            if (ref) ref.onclick = () => { items.forEach(it => { it.style.opacity = '1'; }); };
          })();
        </script>
    </section>
    <section id="tab-doctors" class="tab-section" style="display:none;">
        <div class="section-header"><h2 class="section-title">Add Doctor Profile</h2></div>
        <div id="tab-content-doctors-add" class="section-grid"></div>
        <div class="section-header"><h2 class="section-title">Edit Doctor Schedules</h2></div>
        <div id="tab-content-doctors-sched" class="section-grid"></div>
    </section>
    <section id="tab-services" class="tab-section" style="display:none;">
        <div class="section-header"><h2 class="section-title">Edit Doctor Services</h2></div>
        <div id="tab-content-services-doctor" class="section-grid"></div>
        <div class="section-header"><h2 class="section-title">Edit Service Amounts</h2></div>
        <div id="tab-content-services-amounts" class="section-grid"></div>
    </section>
  
    <?php if (!empty($error_message)): ?>
        <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?=htmlspecialchars($error_message)?></div>
    <?php endif; ?>
    
    <?php if (!empty($success_message)): ?>
        <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 12px; border-radius: 8px; margin-bottom: 20px;"><?=htmlspecialchars($success_message)?></div>
    <?php endif; ?>
    <div class="accordion">
    <div class="accordion-item" id="acc-new-user">
        <div class="accordion-header"><span>New User</span><span class="chev">▾</span></div>
        <div class="accordion-content">
            <div id="content-new-user" class="section-grid"></div>
        </div>
    </div>
    <div class="accordion-item" id="acc-doctor-mgmt">
        <div class="accordion-header"><span>Doctor Management</span><span class="chev">▾</span></div>
        <div class="accordion-content">
            <div id="content-doctor-mgmt" class="section-grid">
                <div class="admin-col">
                    <div class="admin-card">
                      <h2 id="title-edit-doctor-schedules" style="display:none">Edit Doctor Schedules</h2>
                      <form id="doctorScheduleForm" method="post">
                        <table class="admin-table">
                          <thead>
                            <tr>
                              <th>Name</th>
                              <th>Specialization</th>
                              <th>Schedule</th>
                              <th>Actions</th>
                            </tr>
                          </thead>
                          <tbody id="schedule-table-body">
                            <!-- Rows will be populated dynamically -->
                          </tbody>
                        </table>
                        <button type="submit" class="admin-btn">Save</button>
                      </form>
                    </div>
                </div>
                <script>
                document.addEventListener('DOMContentLoaded', () => {
                  const tableBody = document.getElementById('schedule-table-body');
                  const DAYS = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                  const TIMES = ['08:00AM - 09:00AM','09:00AM - 10:00AM','10:00AM - 11:00AM','11:00AM - 12:00PM','01:00PM - 02:00PM','02:00PM - 03:00PM','03:00PM - 04:00PM','04:00PM - 05:00PM'];

                  fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_get_doctor_schedules.php')
                    .then(res => res.json())
                    .then(data => {
                      if (data.status === 'success') {
                        data.data.forEach(doc => {
                          const row = document.createElement('tr');
                          row.dataset.userId = doc.user_id;
                          row.innerHTML = `
                            <td>
                              <div class="table-inline-fields">
                                <input type="text" name="first_name_${doc.user_id}" id="first_${doc.user_id}" class="admin-input" value="${doc.first_name || ''}" placeholder="First name">
                                <input type="text" name="last_name_${doc.user_id}" id="last_${doc.user_id}" class="admin-input" value="${doc.last_name || ''}" placeholder="Last name">
                              </div>
                            </td>
                            <td>
                              <input type="text" name="specialization_${doc.user_id}" id="spec_${doc.user_id}" class="admin-input" value="${doc.specialization || ''}" placeholder="Specialization">
                            </td>
                            <td>
                              <div style="display:flex; gap:0.5rem;">
                                <select name="day_${doc.user_id}" id="day_${doc.user_id}" class="admin-select" style="flex:1"></select>
                                <select name="time_${doc.user_id}" id="time_${doc.user_id}" class="admin-select" style="flex:1"></select>
                              </div>
                            </td>
                            <td>
                              <div class="row-actions">
                                <button type="button" class="btn btn-secondary btn-edit" data-user="${doc.user_id}">Edit</button>
                                <button type="button" class="btn btn-danger btn-delete" data-user="${doc.user_id}">Delete</button>
                              </div>
                            </td>
                          `;
                          tableBody.appendChild(row);

                          const daySel = row.querySelector(`#day_${doc.user_id}`);
                          const timeSel = row.querySelector(`#time_${doc.user_id}`);
                          DAYS.forEach(d => { const opt = document.createElement('option'); opt.value = d; opt.textContent = d; daySel.appendChild(opt); });
                          TIMES.forEach(t => { const opt = document.createElement('option'); opt.value = t; opt.textContent = t; timeSel.appendChild(opt); });

                          const m = (doc.schedule || '').match(/^([A-Za-z]+)\s*-\s*(.+)$/);
                          const preDay = m ? m[1] : '';
                          const preTime = m ? m[2] : '';
                          if (DAYS.includes(preDay)) daySel.value = preDay;
                          if (TIMES.includes(preTime)) timeSel.value = preTime;

                          const editBtn = row.querySelector('.btn-edit');
                          const deleteBtn = row.querySelector('.btn-delete');
                          const firstInput = row.querySelector(`#first_${doc.user_id}`);
                          editBtn?.addEventListener('click', () => { (firstInput || daySel).focus(); (firstInput || daySel).scrollIntoView({behavior:'smooth', block:'center'}); row.style.background = '#f8fafc'; setTimeout(()=>{ row.style.background = ''; }, 800); });
                          deleteBtn?.addEventListener('click', () => {
                            if (!confirm('Delete this doctor from the system?')) return;
                            const fd = new FormData();
                            fd.append('user_id', String(doc.user_id));
                            fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_delete_doctor.php', { method: 'POST', body: fd })
                              .then(res => res.json())
                              .then(data => {
                                if (data.status === 'success') {
                                  row.remove();
                                  alert('✅ Doctor deleted.');
                                } else {
                                  alert('❌ Error: ' + (data.message || 'Failed to delete'));
                                }
                              })
                              .catch(err => { console.error('Delete error:', err); alert('❌ Failed to delete doctor.'); });
                          });
                        });
                      } else {
                        tableBody.innerHTML = `<tr><td colspan="3">❌ Failed to load schedules.</td></tr>`;
                      }
                    })
                    .catch(err => {
                      console.error('Fetch error:', err);
                      tableBody.innerHTML = `<tr><td colspan="3">❌ Error loading schedules.</td></tr>`;
                    });

                  document.getElementById('doctorScheduleForm').addEventListener('submit', function (e) {
                    e.preventDefault();
                    const formData = new FormData(this);

                    document.querySelectorAll('#schedule-table-body tr').forEach(tr => {
                      const uid = tr.dataset.userId;
                      if (!uid) return;
                      const day = tr.querySelector(`#day_${uid}`)?.value || '';
                      const time = tr.querySelector(`#time_${uid}`)?.value || '';
                      if (day && time) formData.set(`schedule_${uid}`, `${day} - ${time}`);
                    });

                    fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_update_doctor_schedules.php', {
                      method: 'POST',
                      body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                      if (data.status === 'success') {
                        alert('✅ Schedules updated successfully!');
                      } else {
                        alert('❌ Error: ' + data.message);
                      }
                    })
                    .catch(err => {
                      console.error('Submission error:', err);
                      alert('❌ Failed to update schedules.');
                    });
                  });
                });
                </script>
            </div>
        </div>
    </div>
    <div class="accordion-item" id="acc-services">
        <div class="accordion-header"><span>Service Managements</span><span class="chev">▾</span></div>
        <div class="accordion-content">
            <div id="content-services" class="section-grid"></div>
        </div>
    </div>
    
<section class="admin-section">
        <div class="section-grid">
        <!-- Register + Doctor Profile -->
        <div class="admin-col">
            <div class="admin-card">
                <h2 id="title-register-user">Register New User</h2>
                <form method="post" autocomplete="off">
                    <div style="margin-bottom: 14px;">
                        <label class="admin-label">First Name</label>
                        <input type="text" name="first_name" class="admin-input" required>
                    </div>
                    <div style="margin-bottom: 14px;">
                        <label class="admin-label">Last Name</label>
                        <input type="text" name="last_name" class="admin-input" required>
                    </div>
                    <div style="margin-bottom: 14px;">
                        <label class="admin-label">Email</label>
                        <input type="email" name="email" class="admin-input" required>
                    </div>
                    <div style="margin-bottom: 14px;">
                        <label class="admin-label">Mobile Number</label>
                        <input type="text" name="contact" class="admin-input" placeholder="e.g. 639XXXXXXXXX" required>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label class="admin-label">Password</label>
                        <input type="password" name="password" class="admin-input" required>
                    </div>
                    <div style="margin-bottom: 14px;">
                        <label class="admin-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="admin-input" required>
                    </div>
                    <div style="margin-bottom: 14px;">
                        <label class="admin-label">Role</label>
                        <select name="role" class="admin-select" id="role-select" required onchange="toggleDoctorFields()">
                            <option value="">Select role</option>
                            <option value="staff">Staff</option>
                            <option value="clerk">Clerk</option>
                           
                        </select>
                    </div>
                    <div id="doctor-fields" style="display:none;">
                        <div style="margin-bottom: 14px;">
                            <label class="admin-label">Specialization</label>
                            <input type="text" name="specialization" class="admin-input">
                        </div>
                        <div style="margin-bottom: 14px;">
                            <label class="admin-label">Schedule</label>
                            <input type="text" name="schedule" class="admin-input" placeholder="e.g. Mon-Fri 8AM-5PM">
                        </div>
                    </div>
                    <button type="submit" name="register_user" class="admin-btn">Register</button>
                </form>
                <script>
                document.addEventListener('DOMContentLoaded', () => {
                const form = document.querySelector('form');

                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const formData = new FormData(form);

                    // Validate password match
                    const password = formData.get('password');
                    const confirm = formData.get('confirm_password');
                    if (password !== confirm) {
                    alert('Passwords do not match.');
                    return;
                    }

                    // Map frontend role to backend role
                    const roleInput = formData.get('role');
                    let backendRole = '';
                    if (roleInput === 'staff' || roleInput === 'midwife') {
                    backendRole = 'staff';
                    } else if (roleInput === 'clerk') {
                    backendRole = 'clerk';
                    } else {
                    alert('Invalid role selected.');
                    return;
                    }
                    formData.set('role', backendRole);

                    // Send request to backend
                    fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_create_new_user.php', {
                    method: 'POST',
                    body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                        alert('✅ User registered successfully!');
                        form.reset();
                        document.getElementById('doctor-fields').style.display = 'none';
                        } else {
                        alert('❌ Error: ' + data.message);
                        }
                    })
                    .catch(err => {
                        console.error('Submission error:', err);
                        alert('❌ Failed to submit form.');
                    });
                });

                // Toggle doctor fields
                document.getElementById('role-select').addEventListener('change', function () {
                    const selected = this.value;
                    const doctorFields = document.getElementById('doctor-fields');
                    doctorFields.style.display = selected === 'doctor' ? 'block' : 'none';
                });
                });
                </script>
            </div>
        </div>
        <!-- Left: Doctor Transformation -->
        <div class="admin-col">
        <div class="admin-card">
            <h2 id="title-add-doctor">Add Doctor Profile</h2>
            <form id="doctorForm" method="post">
            <div style="margin-bottom: 14px;">
                <label class="admin-label">Select Staff User</label>
                <select name="user_id" id="staff-select" class="admin-select" required></select>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="admin-label">Specialization</label>
                <input type="text" name="specialization" class="admin-input" required>
            </div>

            <div style="margin-bottom: 14px;">
                <label class="admin-label">Schedule</label>
                <input type="text" name="schedule" class="admin-input" placeholder="e.g. Mon-Fri 8AM-5PM" required>
            </div>

            <button type="submit" class="admin-btn">Save Doctor Profile</button>
            </form>
        </div>
        </div>
        </div>
    </section>
<section class="admin-section">
    <div class="section-grid">
<!-- Edit Doctor Services -->
<div class="admin-col">
  <div class="admin-card">
    <h2 id="title-edit-doctor-services">Edit Doctor Services</h2>
    <form id="doctorServicesForm" method="post">
      <div style="margin-bottom: 14px;">
        <label class="admin-label">Select Doctor</label>
        <select name="user_id" id="doctor-select" class="admin-select" required></select>
      </div>

      <div style="margin-bottom: 14px;">
        <label class="admin-label">Services Doctor Can Perform</label>
        <div id="services-list">
          <!-- Dynamically populated with service_catalog -->
        </div>
        <div id="services-add-inline" style="margin-top: 12px;">
          <input type="text" id="new-service-name" class="admin-input" placeholder="New service name">
          <button type="button" id="add-service-btn" class="btn btn-secondary">Add Service</button>
        </div>
      </div>

      <button type="submit" class="admin-btn">Save Lists</button>
    </form>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('doctorServicesForm');

  form.addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_update_doctor_services.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        alert('✅ Doctor services updated successfully!');
      } else {
        alert('❌ Error: ' + data.message);
      }
    })
    .catch(err => {
      console.error('Submission error:', err);
      alert('❌ Failed to update doctor services.');
    });
  });
});
</script>
<script>
// Fetch service catalog and populate checkboxes
document.addEventListener('DOMContentLoaded', () => {
  const servicesContainer = document.getElementById('services-list');

  fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_get_service_catalog.php')
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        data.data.forEach(service => {
          const wrapper = document.createElement('div');
          wrapper.style.marginBottom = '6px';

          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.name = 'services[]';
          checkbox.value = service.id;   // ✅ matches alias in PHP
          checkbox.id = 'service_' + service.id;

          const label = document.createElement('label');
          label.htmlFor = checkbox.id;
          label.textContent = `${service.name} (₱${service.amount})`;

          wrapper.appendChild(checkbox);
          wrapper.appendChild(label);
          servicesContainer.appendChild(wrapper);
        });
      } else {
        servicesContainer.textContent = '❌ Failed to load services.';
      }
    })
    .catch(err => {
      console.error('Error loading services:', err);
      servicesContainer.textContent = '❌ Error loading services.';
    });
});

</script>
<script>
// Add new service to catalog and select it for the chosen doctor
document.addEventListener('DOMContentLoaded', () => {
  const addBtn = document.getElementById('add-service-btn');
  if (addBtn) {
    addBtn.addEventListener('click', () => {
      const nameEl = document.getElementById('new-service-name');
      const amtEl = document.getElementById('new-service-amount');
      const name = (nameEl?.value || '').trim();
      const amount = parseFloat(amtEl?.value || '0');
      if (!name) { alert('Please enter a service name'); return; }
      if (isNaN(amount) || amount < 0) { alert('Please enter a valid amount'); return; }
      const fd = new FormData();
      fd.append('name', name);
      fd.append('amount', amount.toFixed(2));
      fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_add_service_catalog.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
          if (data.status === 'success' && data.id) {
            const servicesContainer = document.getElementById('services-list');
            const wrapper = document.createElement('div');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'services[]';
            checkbox.value = String(data.id);
            checkbox.id = 'service_' + data.id;
            checkbox.checked = true;
            const label = document.createElement('label');
            label.htmlFor = checkbox.id;
            label.textContent = `${name} (₱${amount.toFixed(2)})`;
            wrapper.appendChild(checkbox);
            wrapper.appendChild(label);
            servicesContainer.appendChild(wrapper);
            nameEl.value = '';
            amtEl.value = '';
            alert('✅ Service added and selected.');
          } else {
            alert('❌ Error: ' + (data.message || 'Failed to add service'));
          }
        })
        .catch(err => { console.error('Add service error:', err); alert('❌ Failed to add service.'); });
    });
  }
});
</script>
<script>
    // Fetch Doctors
document.addEventListener('DOMContentLoaded', () => {
  const select = document.getElementById('doctor-select');

  fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_get_doctors.php')
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        data.data.forEach(doc => {
          const opt = document.createElement('option');
          opt.value = doc.user_id;
          opt.textContent = `${doc.first_name} ${doc.last_name} (${doc.specialization})`;
          select.appendChild(opt);
        });
      } else {
        alert('❌ Error: ' + data.message);
      }
    })
    .catch(err => {
      console.error('Fetch error:', err);
      alert('❌ Failed to load doctors.');
    });
});
</script>
<script>
// Fetch staff users who are not yet doctors and populate the select dropdown
fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_get_staff_list.php')
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      const select = document.getElementById('staff-select');
      data.data.forEach(user => {
        const opt = document.createElement('option');
        opt.value = user.id;
        opt.textContent = `${user.first_name} ${user.last_name} (${user.email})`;
        select.appendChild(opt);
      });
    } else {
      alert('❌ Error: ' + data.message);
    }
  })
  .catch(err => {
    console.error('Fetch error:', err);
    alert('❌ Failed to load staff list.');
  });
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('doctorForm');

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(form);

    fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_add_doctor.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        alert('✅ Doctor profile created successfully!');
        form.reset();
      } else {
        alert('❌ Error: ' + data.message);
      }
    })
    .catch(err => {
      console.error('Submission error:', err);
      alert('❌ Failed to submit form.');
    });
  });
});
</script>                    
        <!-- Right: Management Panels -->
        <div class="admin-col">

            <!-- Service Amounts -->
            <div class="admin-card">
            <h2 id="title-edit-service-amounts">Edit Service Amounts</h2>
            <form id="serviceAmountForm" method="post">
                <table class="admin-table">
                <thead>
                    <tr>
                    <th>Service</th>
                    <th>Amount (₱)</th>
                    </tr>
                </thead>
                <tbody id="serviceAmountTableBody">
                    <!-- Rows will be injected here -->
                </tbody>
                </table>
                <button type="submit" class="admin-btn">Save Amounts</button>
            </form>
            </div>
        </div>
    </section>
            <script>
            document.addEventListener('DOMContentLoaded', () => {
            const tableBody = document.getElementById('serviceAmountTableBody');

            fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_get_service_catalog.php')
                .then(res => res.json())
                .then(data => {
                if (data.status !== 'success') {
                    alert('Failed to load service catalog');
                    return;
                }

                tableBody.innerHTML = ''; // Clear existing rows

                data.data.forEach(service => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                    <td>${service.name}</td>
                    <td>
                        <input type="number"
                            name="service_amount_${service.id}"
                            class="admin-input"
                            min="0"
                            step="0.01"
                            value="${parseFloat(service.amount).toFixed(2)}">
                    </td>
                    `;
                    tableBody.appendChild(row);
                });
                })
                .catch(err => {
                console.error('Fetch error:', err);
                alert('Unable to load services.');
                });

            // Handle form submission
            document.getElementById('serviceAmountForm').addEventListener('submit', function (e) {
                e.preventDefault();

                const formData = new FormData(this);

                fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_update_service_catalog.php', {
                method: 'POST',
                body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                    alert('✅ Service amounts updated successfully!');
                    } else {
                    alert('❌ Error: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error('Update error:', err);
                    alert('❌ Failed to update service catalog.');
                });
            });
            });
            </script>
            <script>
            document.querySelector('form').addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('http://localhost/JAM_Lyingin/auth/action/admin/admin_update_service_catalog.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                if (data.status === 'success') {
                    alert('✅ Service amounts updated successfully!');
                } else {
                    alert('❌ Error: ' + data.message);
                }
                })
                .catch(err => {
                console.error('Update error:', err);
                alert('❌ Failed to update service catalog.');
                });
            });
            </script>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
      const moveCardByTitle = (titleId, targetId) => {
        const title = document.getElementById(titleId);
        if (!title) return;
        const card = title.closest('.admin-card');
        const parentCol = card ? card.parentElement : null;
        const target = document.getElementById(targetId);
        if (card && target) target.appendChild(parentCol && parentCol.classList.contains('admin-col') ? parentCol : card);
      };
      moveCardByTitle('title-register-user', 'tab-content-new-user');
      moveCardByTitle('title-add-doctor', 'tab-content-doctors-add');
      moveCardByTitle('title-edit-doctor-schedules', 'tab-content-doctors-sched');
      moveCardByTitle('title-edit-doctor-services', 'tab-content-services-doctor');
      moveCardByTitle('title-edit-service-amounts', 'tab-content-services-amounts');

      document.querySelectorAll('.accordion-header').forEach(h => {
        h.addEventListener('click', () => {
          const item = h.parentElement;
          const alreadyOpen = item.classList.contains('open');
          document.querySelectorAll('.accordion-item').forEach(i => i.classList.remove('open'));
          if (!alreadyOpen) item.classList.add('open');
        });
      });
    });
    </script>

    <!-- Admin Footer -->
    <div class="admin-footer">
        <div class="footer-left">
            <div class="footer-sep">•</div>
            <div class="footer-label">Admin Panel v1.0</div>
        </div>
        <div class="footer-right">
            Last updated: <?=date('M d, Y H:i')?>
        </div>
    </div>
</div>
<script>
function toggleDoctorFields() {
    var role = document.getElementById('role-select').value;
    document.getElementById('doctor-fields').style.display = (role === 'doctor') ? 'block' : 'none';
}
</script>
</body>
</html>