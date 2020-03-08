    (function($) {
    "use strict";

    var path = window.location.href;
        $("#layoutSidenav_nav .sb-sidenav a.nav-link").each(function() {
            if (this.href === path) {
                $(this).addClass("active");
                if(this.href.endsWith('resetpw') || this.href.endsWith('adduser') || this.href.endsWith('removeuser') || this.href.endsWith('enableuser') || this.href.endsWith('disableuser')) {
                  document.getElementById("usersNavItem").classList.remove("collapsed");
                  document.getElementById("collapseLayouts").classList.add("show");
                  document.getElementById("individualUserNavItem").classList.remove("collapsed");
                  document.getElementById("individualUserNavItems").classList.add("show");
                }
                if(this.href.endsWith('resetpwbulk') || this.href.endsWith('addbulkusers') || this.href.endsWith('removebulkusers')) {
                  document.getElementById("usersNavItem").classList.remove("collapsed");
                  document.getElementById("collapseLayouts").classList.add("show");
                  document.getElementById("bulkManageNavItem").classList.remove("collapsed");
                  document.getElementById("bulkManageNavItems").classList.add("show");
                }
                if(this.href.endsWith('addusertemplate') || this.href.endsWith('removeusertemplate')) {
                  document.getElementById("usersNavItem").classList.remove("collapsed");
                  document.getElementById("collapseLayouts").classList.add("show");
                  document.getElementById("templatesNavItem").classList.remove("collapsed");
                  document.getElementById("templatesNavItems").classList.add("show");
                }
                if(this.href.endsWith('resetprintqueue')) {
                  document.getElementById("printingNavItem").classList.remove("collapsed");
                  document.getElementById("collapsePages").classList.add("show");
                }
            }
        });

    $("#sidebarToggle").on("click", function(e) {
        e.preventDefault();
        $("body").toggleClass("sb-sidenav-toggled");
    });
})(jQuery);
