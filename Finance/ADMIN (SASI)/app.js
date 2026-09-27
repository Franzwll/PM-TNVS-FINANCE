(function(){
	'use strict';

	function qs(sel, root){ return (root||document).querySelector(sel); }
	function qsa(sel, root){ return Array.from((root||document).querySelectorAll(sel)); }

	var app = qs('.app');
	var sidebar = qs('#sidebar');
	var overlay = qs('#overlay');
	var toggleBtn = qs('#menuToggle');

	function isMobile(){ return window.matchMedia('(max-width: 900px)').matches; }

	function openMobileSidebar(){
		if (!sidebar) return;
		sidebar.classList.add('open');
		if (overlay) overlay.classList.add('show');
		if (toggleBtn) toggleBtn.classList.add('sidebar-open');
	}

	function closeMobileSidebar(){
		if (!sidebar) return;
		sidebar.classList.remove('open');
		if (overlay) overlay.classList.remove('show');
		if (toggleBtn) toggleBtn.classList.remove('sidebar-open');
	}

	function toggleDesktopSidebar(){
		if (!app) return;
		var collapsed = app.classList.toggle('sidebar-collapsed');
		if (toggleBtn) toggleBtn.classList.toggle('sidebar-open', collapsed);
	}

	function handleToggle(){
		if (isMobile()) {
			if (sidebar.classList.contains('open')) closeMobileSidebar();
			else openMobileSidebar();
		} else {
			toggleDesktopSidebar();
		}
	}

	function handleOverlayClick(){
		if (isMobile()) closeMobileSidebar();
	}

	function handleNavClick(){
		if (isMobile()) closeMobileSidebar();
	}

	function handleResize(){
		// Ensure proper state when switching breakpoints
		if (!isMobile()) {
			closeMobileSidebar();
		} else {
			// keep desktop collapsed state but no visual when mobile
		}
	}

	// Bind events
	if (toggleBtn) toggleBtn.addEventListener('click', handleToggle);
	if (overlay) overlay.addEventListener('click', handleOverlayClick);
	qsa('.sidebar .nav').forEach(function(a){
		a.addEventListener('click', handleNavClick);
	});
	window.addEventListener('resize', handleResize);

	// Improve focus outline for accessibility
	qsa('a.nav').forEach(function(a){
		a.addEventListener('focus', function(){ a.classList.add('focus'); });
		a.addEventListener('blur', function(){ a.classList.remove('focus'); });
	});
})();
