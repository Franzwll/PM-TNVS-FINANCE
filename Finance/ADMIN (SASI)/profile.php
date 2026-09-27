<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TNVS - Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="app-header">
        <div class="brand" role="img" aria-label="TNVS Finance System">
            <svg viewBox="0 0 120 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <text x="10" y="16" font-size="16" font-weight="800" letter-spacing="0.5" font-family="Segoe UI, Roboto, Helvetica, Arial, sans-serif" fill="currentColor">TNVS</text>
            </svg>
        </div>
        <div class="user">
            <a href="dashboard.php" class="text-btn">Dashboard</a>
        </div>
    </header>

    <main class="content">
        <section class="section active">
            <h3>User Profile</h3>
            <div class="modal-card" style="display:block">
                <div class="modal-header">
                    <h4 id="profileTitle">User Profile</h4>
                    <div class="header-actions">
                        <button id="editProfileBtn" class="btn-small">Edit Profile</button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="profile-head">
                        <div class="avatar-container">
                            <div class="avatar" aria-hidden="true" id="pfAvatar">A</div>
                            <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
                            <button type="button" id="changeAvatarBtn" class="avatar-change-btn" style="display: none;">
                                <span>📷</span>
                            </button>
                        </div>
                        <div>
                            <div class="name" id="pfName">Admin</div>
                            <div class="meta"><span id="pfRole">Admin</span> • <span id="pfStatus">Active</span></div>
                        </div>
                    </div>

                    <form id="profileForm">
                        <div class="detail-section">
                            <h5>Personal Information</h5>
                            <div class="detail-grid">
                                <div><label>Full name</label><input type="text" id="pfFullNameInput" value="Admin" disabled></div>
                                <div><label>Email</label><input type="email" id="pfEmailInput" value="admin@transportnetwork.com" disabled></div>
                                <div><label>Phone</label><input type="tel" id="pfPhoneInput" value="+63 912 345 6789" disabled></div>
                                <div><label>Address</label><input type="text" id="pfAddressInput" value="Manila, Philippines" disabled></div>
                            </div>
                        </div>

                        <div class="detail-section">
                            <h5>Work Information</h5>
                            <div class="detail-grid">
                                <div><label>Employee ID</label><input type="text" id="pfEmployeeIdInput" value="EMP001" disabled></div>
                                <div><label>Department</label><input type="text" id="pfDepartmentInput" value="IT" disabled></div>
                                <div><label>Position</label><input type="text" id="pfPositionInput" value="System Administrator" disabled></div>
                                <div><label>Hire Date</label><input type="date" id="pfHireDateInput" value="2023-01-15" disabled></div>
                            </div>
                        </div>

                        <div class="form-actions" style="display: none;">
                            <button type="submit" class="btn-small">Save Changes</button>
                            <button type="button" id="cancelEditBtn" class="btn-small">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <div id="toast" class="toast" style="display:none"></div>

    <script>
    (function(){
        function qs(s, r){ return (r||document).querySelector(s); }
        function qsa(s, r){ return Array.from((r||document).querySelectorAll(s)); }

        var profileModal = document; // we reuse functions without real modal

        function enableProfileEdit() {
            qsa('#profileForm input').forEach(function(input){ input.disabled = false; });
            qs('.form-actions').style.display = 'flex';
            qs('#changeAvatarBtn').style.display = 'flex';
            qs('#editProfileBtn').textContent = 'Editing...';
            qs('#editProfileBtn').disabled = true;
        }
        function resetProfileForm() {
            qsa('#profileForm input').forEach(function(input){ input.disabled = true; });
            qs('.form-actions').style.display = 'none';
            qs('#changeAvatarBtn').style.display = 'none';
            var file = qs('#avatarUpload'); if (file) file.value = '';
            qs('#editProfileBtn').textContent = 'Edit Profile';
            qs('#editProfileBtn').disabled = false;
        }
        function saveProfileChanges(e) {
            if (e) e.preventDefault();
            var updatedName = qs('#pfFullNameInput').value;
            qs('#pfName').textContent = updatedName;
            var avatar = qs('#pfAvatar');
            if (!avatar.style.backgroundImage || avatar.style.backgroundImage === 'none') {
                avatar.textContent = updatedName.charAt(0);
                avatar.style.backgroundImage = 'none';
            }
            resetProfileForm();
            var el = document.getElementById('toast');
            if (el){
                el.className = 'toast success';
                el.textContent = 'Save successful';
                el.style.display = 'block';
                void el.offsetWidth; el.classList.add('show');
                setTimeout(function(){ el.classList.remove('show'); setTimeout(function(){ el.style.display='none'; }, 300); }, 2000);
            }
        }

        qs('#editProfileBtn') && qs('#editProfileBtn').addEventListener('click', enableProfileEdit);
        qs('#cancelEditBtn') && qs('#cancelEditBtn').addEventListener('click', resetProfileForm);
        qs('#profileForm') && qs('#profileForm').addEventListener('submit', saveProfileChanges);
        qs('#changeAvatarBtn') && qs('#changeAvatarBtn').addEventListener('click', function(){ qs('#avatarUpload').click(); });
        qs('#avatarUpload') && qs('#avatarUpload').addEventListener('change', function(e){
            var file = e.target.files[0];
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function(ev){ var a = qs('#pfAvatar'); a.style.backgroundImage = 'url(' + ev.target.result + ')'; a.textContent = ''; };
            reader.readAsDataURL(file);
        });
    })();
    </script>
</body>
</html>


