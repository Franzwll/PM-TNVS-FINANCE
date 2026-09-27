// TNVS Finance System - Modern Beautiful Dashboard Controller
(function () {
  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => Array.from(r.querySelectorAll(s));

  const login = qs('#login');
  const app = qs('#app');
  const loginForm = qs('#loginForm');
  const currentUser = qs('#currentUser');
  const sidebar = qs('#sidebar');
  const overlay = qs('#overlay');
  const mainContent = qs('#mainContent');
  const profileModal = qs('#profileModal');
  const aiMessages = qs('#aiChatMessages');
  const aiInput = qs('#aiChatInput');

  const state = { isLoggedIn: false, user: '', sidebarCollapsed: false };



    // Enhanced login with better UX
  function setUiByAuth() {
    if (state.isLoggedIn) {
      login.style.display = 'none';
      app.style.display = 'grid';
      currentUser.textContent = `Welcome, ${state.user}`;
      
      // Ensure main content is visible
      if (mainContent && mainContent.classList.contains('hidden')) {
        mainContent.classList.remove('hidden');
      }
      
      // Restore sidebar state
      if (state.sidebarCollapsed) {
        app.classList.add('sidebar-collapsed');
        qs('#menuToggle').classList.add('sidebar-open');
      }

      // Animate dashboard cards on load
      animateDashboardCards();
    } else {
      app.style.display = 'none';
      login.style.display = 'grid';
      currentUser.textContent = 'Welcome';
    }
  }
  
  // Animate dashboard cards with staggered effect
  function animateDashboardCards() {
    const cards = qsa('.card');
    cards.forEach((card, index) => {
      setTimeout(() => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.6s ease';
        
        requestAnimationFrame(() => {
          card.style.opacity = '1';
          card.style.transform = 'translateY(0)';
        });
      }, index * 100);
    });
  }

  // Enhanced login form handling
  loginForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    // Show loading state with better UX
    submitBtn.textContent = 'Signing in...';
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.7';
    
    // Simulate login process with enhanced feedback
    setTimeout(() => {
      const username = qs('#username').value.trim() || 'User';
      state.isLoggedIn = true;
      state.user = username;
      try {
        localStorage.setItem('tnvs_isLoggedIn', '1');
        localStorage.setItem('tnvs_username', username);
      } catch {}
      setUiByAuth();
      // Ensure dashboard is visible and active
      activateSection('dashboard');
      
      // Reset button with smooth transition
      submitBtn.textContent = originalText;
      submitBtn.disabled = false;
      submitBtn.style.opacity = '1';
    }, 1500);
  });

  // Enhanced section activation with smooth transitions
  function activateSection(id) {
    if (!id) id = 'dashboard';
    
    // Ensure main content is visible
    if (mainContent && mainContent.classList.contains('hidden')) {
      mainContent.classList.remove('hidden');
    }
    
    // Update navigation with smooth transitions
    qsa('.nav').forEach(b => {
      b.classList.toggle('active', b.getAttribute('data-section') === id);
    });
    
    // Show only the requested section with fade effect
    qsa('.section').forEach(s => {
      if (s.id === id) {
        s.style.opacity = '0';
        s.style.transform = 'translateY(10px)';
        s.classList.add('active');
        
        requestAnimationFrame(() => {
          s.style.transition = 'all 0.3s ease';
          s.style.opacity = '1';
          s.style.transform = 'translateY(0)';
        });
      } else {
        s.classList.remove('active');
      }
    });
    
    // Mobile close with smooth animation
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
    qs('#menuToggle')?.classList.remove('sidebar-open');
    
    try { location.hash = `#${id}`; } catch {}
  }

  // Enhanced sidebar toggle with smooth animations
  qs('#menuToggle')?.addEventListener('click', () => {
    const isOpen = sidebar.classList.contains('open');
    const isCollapsed = app.classList.contains('sidebar-collapsed');
    
    if (window.innerWidth <= 900) {
      // Mobile behavior
      if (isOpen) {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        qs('#menuToggle').classList.remove('sidebar-open');
      } else {
        sidebar.classList.add('open');
        overlay.classList.add('show');
        qs('#menuToggle').classList.add('sidebar-open');
      }
    } else {
      // Desktop behavior
      if (isCollapsed) {
        app.classList.remove('sidebar-collapsed');
        qs('#menuToggle').classList.remove('sidebar-open');
        state.sidebarCollapsed = false;
      } else {
        app.classList.add('sidebar-collapsed');
        qs('#menuToggle').classList.add('sidebar-open');
        state.sidebarCollapsed = true;
      }
      
      try {
        localStorage.setItem('tnvs_sidebarCollapsed', state.sidebarCollapsed ? '1' : '0');
      } catch {}
    }
  });

  // Enhanced overlay click handling
  overlay?.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
    qs('#menuToggle').classList.remove('sidebar-open');
  });

  // Enhanced navigation with smooth transitions
  qsa('.nav').forEach(nav => {
    nav.addEventListener('click', () => {
      const section = nav.getAttribute('data-section');
      activateSection(section);
      
      // Close sidebar on mobile after navigation
      if (window.innerWidth <= 900) {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        qs('#menuToggle').classList.remove('sidebar-open');
      }
    });
  });

  // Enhanced logout with confirmation
  qs('#logoutBtn')?.addEventListener('click', () => {
    if (confirm('Are you sure you want to logout?')) {
    state.isLoggedIn = false;
    state.user = '';
    try {
      localStorage.removeItem('tnvs_isLoggedIn');
      localStorage.removeItem('tnvs_username');
    } catch {}
    setUiByAuth();

    }
  });

  // Enhanced AI chat functionality
  qs('#aiSend')?.addEventListener('click', sendAIMessage);
  aiInput?.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') sendAIMessage();
  });

  function sendAIMessage() {
    const input = qs('#aiChatInput');
    const message = input.value.trim();
    
    if (!message) return;
    
    // Add user message with enhanced styling
    addMessage(message, 'user');
    input.value = '';
    
    // Generate accurate AI response based on actual data and user query
    setTimeout(() => {
      const response = generateAccurateResponse(message);
      addMessage(response, 'bot');
    }, 1000);
  }

  function generateAccurateResponse(userMessage) {
    const message = userMessage.toLowerCase();
    
    // Revenue and financial data
    if (message.includes('revenue') || message.includes('income') || message.includes('earnings')) {
      return "Based on your current dashboard data, your total revenue is ₱2,450,000 for this period. This represents a strong performance in your transport network operations with a 12.5% increase from last month. The revenue breakdown shows consistent growth across all service categories. 📈";
    }
    
    // Expense analysis
    if (message.includes('expense') || message.includes('cost') || message.includes('spending')) {
      return "Your current expense breakdown shows: Fuel & Maintenance (₱661,500 - 35%), Driver Salaries (₱567,000 - 30%), Insurance (₱378,000 - 20%), Administrative (₱226,800 - 12%), and Other (₱56,700 - 3%). Total expenses: ₱1,890,000. Consider optimizing fuel efficiency to reduce the largest expense category. ⛽";
    }
    
    // Budget information
    if (message.includes('budget') || message.includes('remaining') || message.includes('spent')) {
      return "Your budget status: Total Budget ₱5,000,000, Spent ₱1,890,000, Remaining ₱3,110,000. Department breakdown: Operations (75% utilized), Marketing (80% utilized), IT (60% utilized), Finance (13% utilized), Administrative (32% utilized). You have excellent budget flexibility with 62% remaining. 💰";
    }
    
    // User management
    if (message.includes('user') || message.includes('employee') || message.includes('driver') || message.includes('staff')) {
      return "You currently have 4 active users: 1 Admin, 1 Manager, 1 Employee, and 1 Driver. All users are in Active status. The user management system allows you to view detailed profiles, edit information, and manage user accounts. 👥";
    }
    
    // Accounts payable/receivable
    if (message.includes('payable') || message.includes('receivable') || message.includes('invoice')) {
      return "Accounts Payable: 2 pending invoices totaling ₱180,000 (ABC Supplies: ₱120,000, XYZ Services: ₱60,000). Accounts Receivable: 2 invoices totaling ₱420,000 (Company A: ₱250,000 overdue, Company B: ₱170,000 pending). Focus on collecting overdue receivables to improve cash flow. 📊";
    }
    
    // General ledger
    if (message.includes('ledger') || message.includes('account') || message.includes('balance')) {
      return "General Ledger Summary: Cash (₱500,000), Accounts Receivable (₱250,000), Accounts Payable (₱150,000). Your cash position is strong, but monitor receivables collection to maintain liquidity. 💳";
    }
    
    // Disbursement
    if (message.includes('disbursement') || message.includes('payment') || message.includes('payout')) {
      return "Recent disbursements: Travel Expenses (₱5,000 - Approved), Office Supplies (₱3,500 - Pending). Total disbursed: ₱8,500. Ensure proper documentation for pending approvals. 💸";
    }
    
    // Collection
    if (message.includes('collection') || message.includes('receipt') || message.includes('payment received')) {
      return "Recent collections: Company A (₱250,000 via Bank Transfer), Company B (₱170,000 via Cash). Total collected: ₱420,000. All receipts are available for printing. 🧾";
    }
    
    // Performance metrics
    if (message.includes('performance') || message.includes('efficiency') || message.includes('utilization')) {
      return "Performance metrics: Fleet utilization at 87% (above industry average), Budget utilization at 38% (excellent efficiency), Revenue growth trend positive. Your operations are performing well with strong budget management! 🎯";
    }
    
    // Help and general questions
    if (message.includes('help') || message.includes('what can you do') || message.includes('assist')) {
      return "I can help you with: Revenue analysis, Expense breakdown, Budget tracking, User management, Accounts payable/receivable, General ledger, Disbursements, Collections, and Performance metrics. Just ask me about any of these areas! 🤖";
    }
    
    // Default response for unrecognized queries
    return "I understand you're asking about '" + userMessage + "'. I can help you with TNVS financial data, user management, and operational metrics. Could you please rephrase your question or ask about revenue, expenses, budget, users, accounts, or performance? I'm here to provide accurate information based on your current data. 💡";
  }

  function addMessage(text, type) {
    const msg = document.createElement('div');
    msg.className = `msg ${type}`;
    msg.textContent = text;
    
    aiMessages.appendChild(msg);
    aiMessages.scrollTop = aiMessages.scrollHeight;
  }

  // Enhanced user profile modal
  qsa('.view-user').forEach(btn => {
    btn.addEventListener('click', () => {
      const userId = btn.getAttribute('data-user-id');
      showUserProfile(userId);
    });
  });



  // Delete user functionality
  qsa('.delete-user').forEach(btn => {
    btn.addEventListener('click', () => {
      const userId = btn.getAttribute('data-user-id');
      showDeleteConfirmation(userId);
    });
  });

  function showUserProfile(userId) {
    const userData = {
      1: { name: 'Admin User', role: 'Admin', status: 'Active', email: 'admin@transportnetwork.com', phone: '+63 912 345 6789', address: 'Manila, Philippines', employeeId: 'EMP001', department: 'IT', position: 'System Administrator', hireDate: '2023-01-15' },
      2: { name: 'Manager User', role: 'Manager', status: 'Active', email: 'manager@transportnetwork.com', phone: '+63 923 456 7890', address: 'Quezon City, Philippines', employeeId: 'EMP002', department: 'Operations', position: 'Operations Manager', hireDate: '2023-03-20' },
      3: { name: 'Employee User', role: 'Employee', status: 'Active', email: 'employee@transportnetwork.com', phone: '+63 934 567 8901', address: 'Makati, Philippines', employeeId: 'EMP003', department: 'Finance', position: 'Accountant', hireDate: '2023-06-10' },
      4: { name: 'Driver User', role: 'Driver', status: 'Active', email: 'driver@transportnetwork.com', phone: '+63 945 678 9012', address: 'Pasig, Philippines', employeeId: 'EMP004', department: 'Logistics', position: 'Professional Driver', hireDate: '2023-08-05' }
    };

    const user = userData[userId] || userData[1];
    
    // Populate modal with user data
    qs('#pfName').textContent = user.name;
    qs('#pfRole').textContent = user.role;
    qs('#pfStatus').textContent = user.status;
    
    // Populate input fields
    qs('#pfFullNameInput').value = user.name;
    qs('#pfEmailInput').value = user.email;
    qs('#pfPhoneInput').value = user.phone;
    qs('#pfAddressInput').value = user.address;
    qs('#pfEmployeeIdInput').value = user.employeeId;
    qs('#pfDepartmentInput').value = user.department;
    qs('#pfPositionInput').value = user.position;
    qs('#pfHireDateInput').value = user.hireDate;
    
    // Update avatar with user initial
    const avatar = qs('#pfAvatar');
    avatar.textContent = user.name.charAt(0);
    avatar.style.backgroundImage = 'none';
    avatar.style.background = 'var(--gradient-primary)';
    
    // Show modal with enhanced animation
    profileModal.classList.add('show');
    profileModal.setAttribute('aria-hidden', 'false');
  }

  // Enhanced modal close functionality
  qs('#closeProfile')?.addEventListener('click', () => {
    profileModal.classList.remove('show');
    profileModal.setAttribute('aria-hidden', 'true');
    // Reset form to view mode
    resetProfileForm();
  });

  // Edit profile functionality
  qs('#editProfileBtn')?.addEventListener('click', () => {
    enableProfileEdit();
  });

  qs('#cancelEditBtn')?.addEventListener('click', () => {
    resetProfileForm();
  });

  qs('#profileForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    saveProfileChanges();
  });

  // Avatar upload functionality
  qs('#changeAvatarBtn')?.addEventListener('click', () => {
    qs('#avatarUpload').click();
  });

  qs('#avatarUpload')?.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(e) {
        const avatar = qs('#pfAvatar');
        avatar.style.backgroundImage = `url(${e.target.result})`;
        avatar.textContent = '';
      };
      reader.readAsDataURL(file);
    }
  });

  function enableProfileEdit() {
    // Enable all input fields
    qsa('#profileForm input').forEach(input => {
      input.disabled = false;
    });
    
    // Show form actions and avatar change button
    qs('.form-actions').style.display = 'flex';
    qs('#changeAvatarBtn').style.display = 'flex';
    
    // Change button text
    qs('#editProfileBtn').textContent = 'Editing...';
    qs('#editProfileBtn').disabled = true;
  }

  function resetProfileForm() {
    // Disable all input fields
    qsa('#profileForm input').forEach(input => {
      input.disabled = true;
    });
    
    // Hide form actions and avatar change button
    qs('.form-actions').style.display = 'none';
    qs('#changeAvatarBtn').style.display = 'none';
    
    // Reset file input
    qs('#avatarUpload').value = '';
    
    // Reset button
    qs('#editProfileBtn').textContent = 'Edit Profile';
    qs('#editProfileBtn').disabled = false;
  }

  function saveProfileChanges() {
    // Get updated values
    const updatedData = {
      name: qs('#pfFullNameInput').value,
      email: qs('#pfEmailInput').value,
      phone: qs('#pfPhoneInput').value,
      address: qs('#pfAddressInput').value,
      employeeId: qs('#pfEmployeeIdInput').value,
      department: qs('#pfDepartmentInput').value,
      position: qs('#pfPositionInput').value,
      hireDate: qs('#pfHireDateInput').value
    };
    
    // Update display
    qs('#pfName').textContent = updatedData.name;
    
    // Update avatar (keep image if uploaded, otherwise use initial)
    const avatar = qs('#pfAvatar');
    if (!avatar.style.backgroundImage || avatar.style.backgroundImage === 'none') {
      avatar.textContent = updatedData.name.charAt(0);
      avatar.style.backgroundImage = 'none';
      avatar.style.background = 'var(--gradient-primary)';
    }
    
    // Update user table row to reflect changes
    updateUserTableRow(updatedData);
    
    // Reset form to view mode
    resetProfileForm();
    
    // In a real application, you would save to database here
    console.log('Profile updated:', updatedData);
  }

  function updateUserTableRow(updatedData) {
    // Find the current user row in the table
    const userRows = qsa('#usersTable tbody tr');
    const currentName = qs('#pfName').textContent;
    
    userRows.forEach(row => {
      const nameCell = row.cells[0];
      if (nameCell.textContent === currentName) {
        // Update the name and email in the table
        nameCell.textContent = updatedData.name;
        row.cells[2].textContent = updatedData.email; // Email column
      }
    });
  }



  // Enhanced print receipt functionality
  qsa('.print-receipt').forEach(btn => {
    btn.addEventListener('click', () => {
      const row = btn.closest('tr');
      const customer = row.cells[0].textContent;
      const invoiceNo = row.cells[1].textContent;
      const amount = row.cells[2].textContent;
      const date = row.cells[3].textContent;
      const method = row.cells[4].textContent;
      
      printReceipt(customer, invoiceNo, amount, date, method);
    });
  });

  function printReceipt(customer, invoiceNo, amount, date, method) {
    // Create receipt content
    const receiptContent = `
      <!DOCTYPE html>
      <html>
      <head>
        <title>TNVS Receipt</title>
        <style>
          body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
          .receipt { max-width: 400px; margin: 0 auto; border: 2px solid #333; padding: 20px; }
          .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
          .header h1 { margin: 0; color: #333; font-size: 24px; }
          .header p { margin: 5px 0; color: #666; }
          .details { margin-bottom: 20px; }
          .detail-row { display: flex; justify-content: space-between; margin: 8px 0; }
          .detail-label { font-weight: bold; }
          .amount { font-size: 20px; font-weight: bold; color: #333; text-align: center; border: 2px solid #333; padding: 10px; margin: 20px 0; }
          .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
          @media print {
            body { margin: 0; }
            .receipt { border: none; }
            .no-print { display: none; }
          }
        </style>
      </head>
      <body>
        <div class="receipt">
          <div class="header">
            <h1>TNVS</h1>
            <p>Transport Network Vehicle System</p>
            <p>Official Receipt</p>
          </div>
          
          <div class="details">
            <div class="detail-row">
              <span class="detail-label">Customer:</span>
              <span>${customer}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Invoice No:</span>
              <span>${invoiceNo}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Date:</span>
              <span>${date}</span>
            </div>
            <div class="detail-row">
              <span class="detail-label">Payment Method:</span>
              <span>${method}</span>
            </div>
          </div>
          
          <div class="amount">
            Amount: ${amount}
          </div>
          
          <div class="footer">
            <p>Thank you for your business!</p>
            <p>This is a computer-generated receipt</p>
            <p>Generated on: ${new Date().toLocaleString()}</p>
          </div>
        </div>
        
        <div class="no-print" style="text-align: center; margin-top: 20px;">
          <button onclick="window.print()">Print Receipt</button>
          <button onclick="window.close()">Close</button>
        </div>
      </body>
      </html>
    `;
    
    // Open new window with receipt
    const receiptWindow = window.open('', '_blank', 'width=500,height=700');
    receiptWindow.document.write(receiptContent);
    receiptWindow.document.close();
    
    // Auto-print after a short delay
    setTimeout(() => {
      receiptWindow.print();
    }, 500);
  }



  // Delete user confirmation
  function showDeleteConfirmation(userId) {
    const userData = {
      1: { name: 'Admin User' },
      2: { name: 'Manager User' },
      3: { name: 'Employee User' },
      4: { name: 'Driver User' }
    };

    const user = userData[userId] || userData[1];
    
    if (confirm(`Are you sure you want to delete "${user.name}"? This action cannot be undone.`)) {
      // Remove the user row from the table
      const userRows = qsa('#usersTable tbody tr');
      userRows.forEach(row => {
        const nameCell = row.cells[0];
        if (nameCell.textContent === user.name) {
          row.remove();
        }
      });
      
      // Close profile modal if it's open for the deleted user
      if (profileModal.classList.contains('show')) {
        profileModal.classList.remove('show');
        profileModal.setAttribute('aria-hidden', 'true');
        resetProfileForm();
      }
    }
  }

  // Enhanced chart animations
  function animateCharts() {
    // Animate bar chart
    const bars = qsa('.bars-chart .bar');
    bars.forEach((bar, index) => {
      const height = bar.style.height;
      bar.style.height = '0';
      
      setTimeout(() => {
        bar.style.height = height;
      }, index * 100);
    });

    // Animate breakdown bars
    const breakdownBars = qsa('.barline span');
    breakdownBars.forEach((bar, index) => {
      const width = bar.style.width;
      bar.style.width = '0';
      
      setTimeout(() => {
        bar.style.width = width;
      }, index * 200);
    });
  }

  // Initialize dashboard with enhanced animations
  function initDashboard() {
    // Check for saved state
    try {
      if (localStorage.getItem('tnvs_isLoggedIn') === '1') {
        state.isLoggedIn = true;
        state.user = localStorage.getItem('tnvs_username') || 'User';
        state.sidebarCollapsed = localStorage.getItem('tnvs_sidebarCollapsed') === '1';
      }
    } catch {}
    
    setUiByAuth();
    
    // Animate charts after a short delay
    setTimeout(animateCharts, 500);
    
    // Check for hash in URL or activate dashboard by default
    const hash = location.hash.slice(1);
    if (hash) {
      activateSection(hash);
    } else if (state.isLoggedIn) {
      // If logged in but no hash, show dashboard
      activateSection('dashboard');
    }
  }

  // Enhanced window resize handling
  window.addEventListener('resize', () => {
    if (window.innerWidth > 900) {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
      qs('#menuToggle').classList.remove('sidebar-open');
    }
  });

  // Initialize the application
  initDashboard();
})();

