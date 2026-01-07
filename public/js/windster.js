// Windster JavaScript - Minimal version
// Compatible with all browsers

// Basic sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Windster JS loaded successfully');
    
    // Sidebar toggle functionality
    const sidebarToggle = document.querySelector('[data-drawer-toggle="drawer-navigation"]');
    const sidebar = document.querySelector('#drawer-navigation');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
        });
    }
    
    // Close sidebar when clicking outside
    document.addEventListener('click', function(e) {
        if (sidebar && !sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.add('-translate-x-full');
        }
    });
    
    // Prevent form submission issues
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            console.log('Form submit event triggered');
            // Don't prevent default - let it submit normally
        });
    });
});