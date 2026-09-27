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

  // Enhanced notification system with better animations
  function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Add to body
    document.body.appendChild(notification);
    
    // Show notification with enhanced animation
    requestAnimationFrame(() => {
      notification.classList.add('show');
    });
    
    // Auto remove after 4 seconds
    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => notification.remove(), 300);
    }, 4000);
  }

  // Enhanced login with better UX
  function setUiByAuth() {
    if (state.isLoggedIn) {
      login.style.display = 'none';
      app.style.display = 'grid';
      currentUser.textContent = `Welcome, ${state.user}`;
      
      // Show welcome notification with enhanced message
      showNotification(`Welcome back, ${state.user}! 🎉 Dashboard loaded successfully.`, 'success');
      
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
    
    // Reveal main content after a valid menu click
    if (mainContent && mainContent.classList.contains('hidden')) {
      mainContent.classList.remove('hidden');
    }
    
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
      showNotification('Logged out successfully. 👋', 'info');
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
    
    // Simulate AI response with typing effect
    setTimeout(() => {
      const responses = [
        "Based on the current data, your revenue has increased by 12.5% this month. This is excellent growth! 📈",
        "I can help you analyze the expense breakdown. The largest category is Fuel & Maintenance at 35%. Consider reviewing fuel efficiency strategies.",
        "Your fleet utilization is at 87%, which is above industry average. Great job on operational efficiency! 🚛",
        "The pending payables have decreased by 5.2% from last month. Your cash flow management is improving.",
        "Looking at the budget, you have ₱1,800,000 remaining. This gives you good flexibility for the rest of the quarter."
      ];
      
      const randomResponse = responses[Math.floor(Math.random() * responses.length)];
      addMessage(randomResponse, 'bot');
    }, 1000);
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

  function showUserProfile(userId) {
    const userData = {
      1: { name: 'Admin User', role: 'Administrator', status: 'Active', email: 'admin@transportnetwork.com', phone: '+63 912 345 6789', address: 'Manila, Philippines', employeeId: 'EMP001', department: 'IT', position: 'System Administrator', hireDate: '2023-01-15' },
      2: { name: 'Manager User', role: 'Manager', status: 'Active', email: 'manager@transportnetwork.com', phone: '+63 923 456 7890', address: 'Quezon City, Philippines', employeeId: 'EMP002', department: 'Operations', position: 'Operations Manager', hireDate: '2023-03-20' },
      3: { name: 'Employee User', role: 'Employee', status: 'Active', email: 'employee@transportnetwork.com', phone: '+63 934 567 8901', address: 'Makati, Philippines', employeeId: 'EMP003', department: 'Finance', position: 'Accountant', hireDate: '2023-06-10' },
      4: { name: 'Driver User', role: 'Driver', status: 'Active', email: 'driver@transportnetwork.com', phone: '+63 945 678 9012', address: 'Pasig, Philippines', employeeId: 'EMP004', department: 'Logistics', position: 'Professional Driver', hireDate: '2023-08-05' }
    };

    const user = userData[userId] || userData[1];
    
    // Populate modal with user data
    qs('#pfName').textContent = user.name;
    qs('#pfRole').textContent = user.role;
    qs('#pfStatus').textContent = user.status;
    qs('#pfFullName').textContent = user.name;
    qs('#pfEmail').textContent = user.email;
    qs('#pfPhone').textContent = user.phone;
    qs('#pfAddress').textContent = user.address;
    qs('#pfEmployeeId').textContent = user.employeeId;
    qs('#pfDepartment').textContent = user.department;
    qs('#pfPosition').textContent = user.position;
    qs('#pfHireDate').textContent = user.hireDate;
    
    // Update avatar with user initial
    const avatar = qs('.avatar');
    avatar.textContent = user.name.charAt(0);
    
    // Show modal with enhanced animation
    profileModal.classList.add('show');
    profileModal.setAttribute('aria-hidden', 'false');
  }

  // Enhanced modal close functionality
  qs('#closeProfile')?.addEventListener('click', () => {
    profileModal.classList.remove('show');
    profileModal.setAttribute('aria-hidden', 'true');
  });

  // Enhanced add user modal
  qs('#addUserBtn')?.addEventListener('click', () => {
    qs('#addUserModal').classList.add('show');
    qs('#addUserModal').setAttribute('aria-hidden', 'false');
  });

  qs('#closeAddUser')?.addEventListener('click', () => {
    qs('#addUserModal').classList.remove('show');
    qs('#addUserModal').setAttribute('aria-hidden', 'true');
  });

  // Enhanced add user form
  qs('#addUserForm')?.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const name = qs('#newUserName').value.trim();
    const email = qs('#newUserEmail').value.trim();
    const role = qs('#newUserRole').value;
    const status = qs('#newUserStatus').value;
    
    if (!name || !email) {
      showNotification('Please fill in all required fields.', 'warning');
      return;
    }
    
    // Simulate adding user
    showNotification(`User "${name}" added successfully! 🎉`, 'success');
    
    // Reset form
    e.target.reset();
    
    // Close modal
    qs('#addUserModal').classList.remove('show');
    qs('#addUserModal').setAttribute('aria-hidden', 'true');
  });

  // Enhanced print receipt functionality
  qsa('.print-receipt').forEach(btn => {
    btn.addEventListener('click', () => {
      showNotification('Receipt printed successfully! 🖨️', 'success');
    });
  });

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
    
    // Check for hash in URL
    const hash = location.hash.slice(1);
    if (hash) {
      activateSection(hash);
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

