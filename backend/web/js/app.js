
$(function () {
  'use strict'

  feather.replace();

  // Initialize PerfectScrollbar of navbar menu for mobile only
  let psNavbar = null;
  if (window.matchMedia('(max-width: 991px)').matches && typeof PerfectScrollbar !== 'undefined') {
    psNavbar = new PerfectScrollbar('#navbarMenu', {
      suppressScrollX: true
    });
  }

  // Showing sub-menu of active menu on navbar when mobile
  function showNavbarActiveSub() {
    if (window.matchMedia('(max-width: 991px)').matches) {
      $('#navbarMenu .active').addClass('show');
    } else {
      $('#navbarMenu .active').removeClass('show');
    }
  }

  showNavbarActiveSub()
  $(window).resize(function () {
    showNavbarActiveSub()
  })

  // Initialize backdrop for overlay purpose
  $('body').append('<div class="backdrop"></div>');


  // Showing sub menu of navbar menu while hiding other siblings
  $('.navbar-menu .with-sub .nav-link').on('click', function (e) {
    e.preventDefault();
    $(this).parent().toggleClass('show');
    $(this).parent().siblings().removeClass('show');

    if (psNavbar) {
      psNavbar.update();
    }
  })

  // Closing dropdown menu of navbar menu
  $(document).on('click touchstart', function (e) {
    e.stopPropagation();

    // closing nav sub menu of header when clicking outside of it
    if (window.matchMedia('(min-width: 992px)').matches) {
      var navTarg = $(e.target).closest('.navbar-menu .nav-item').length;
      if (!navTarg) {
        $('.navbar-header .nav-item').removeClass('show');
      }
    }
  });

  $('#mainMenuClose').on('click', function (e) {
    e.preventDefault();
    $('body').removeClass('navbar-nav-show');
  });

  $('#sidebarMenuOpen').on('click', function (e) {
    e.preventDefault();
    $('body').addClass('sidebar-show');
  });


  // Initialize PerfectScrollbar for sidebar menu
  if ($('#sidebarMenu').length) {
    let psSidebar = null;
    if (typeof PerfectScrollbar !== 'undefined') {
      psSidebar = new PerfectScrollbar('#sidebarMenu', {
        suppressScrollX: true
      });
    }


    // Showing sub menu in sidebar
    $('.sidebar-nav .with-sub').on('click', function (e) {
      e.preventDefault();
      $(this).parent().toggleClass('show');

      if (psSidebar) {
        psSidebar.update();
      }
    })
  }


  $('#mainMenuOpen').on('click touchstart', function (e) {
    e.preventDefault();
    $('body').addClass('navbar-nav-show');
  })

  $('#sidebarMenuClose').on('click', function (e) {
    e.preventDefault();
    $('body').removeClass('sidebar-show');
  })

  // hide sidebar when clicking outside of it
  $(document).on('click touchstart', function (e) {
    e.stopPropagation();

    // closing of sidebar menu when clicking outside of it
    if (!$(e.target).closest('.burger-menu').length) {
      var sb = $(e.target).closest('.sidebar').length;
      var nb = $(e.target).closest('.navbar-menu-wrapper').length;
      if (!sb && !nb) {
        if ($('body').hasClass('navbar-nav-show')) {
          $('body').removeClass('navbar-nav-show');
        } else {
          $('body').removeClass('sidebar-show');
        }
      }
    }
  });

});
