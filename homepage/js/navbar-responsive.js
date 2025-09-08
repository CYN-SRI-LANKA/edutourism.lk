// Direct Toggle Mobile JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Create mobile menu button
    const mobileBtn = document.createElement('div');
    mobileBtn.className = 'mobile-menu-btn';
    mobileBtn.innerHTML = '<span></span><span></span><span></span>';
    
    // Insert the button at the top of the container
    const navContainer = document.querySelector('.nav-item .container');
    if (navContainer) {
      navContainer.insertBefore(mobileBtn, navContainer.firstChild);
    }
    
    // Get the existing menu
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.nav-menu.mobile-menu');
    
    // Add click handler to toggle menu visibility
    if (mobileMenuBtn && mobileMenu) {
      mobileMenuBtn.addEventListener('click', function(e) {
        e.preventDefault();
        mobileMenuBtn.classList.toggle('active');
        mobileMenu.classList.toggle('active');
        
        // Force all menu items to be visible
        const menuItems = mobileMenu.querySelectorAll('ul li');
        menuItems.forEach(function(item) {
          item.style.display = 'block';
        });
      });
    }
    
    // Check for existing slicknav or other mobile menu plugins
    // and disable them if they exist
    if (window.jQuery) {
      if (jQuery.fn.slicknav) {
        jQuery.fn.slicknav = function() { return this; };
      }
    }
    
    // Hide any other mobile menu systems that might be interfering
    document.querySelectorAll('.slicknav_menu, .mean-container, .responsive-menu-container').forEach(function(elem) {
      if (elem) elem.style.display = 'none';
    });
  });