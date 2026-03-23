(function () {
  "use strict";

  document.addEventListener("DOMContentLoaded", function () {
    // ===== DROPDOWN DESKTOP (Examens etc.) =====
    const dropdownToggles = document.querySelectorAll(".dropdown-toggle");

    dropdownToggles.forEach((toggle) => {
      toggle.addEventListener("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const dropdown = this.closest(".dropdown");
        const isOpen = dropdown.classList.contains("open");

        // Ferme tous les dropdowns ouverts
        closeAllDropdowns();

        if (!isOpen) {
          dropdown.classList.add("open");

          // Force le z-index au-dessus de tout
          const content = dropdown.querySelector(".dropdown-content");
          if (content) {
            content.style.zIndex = "99999";
            content.style.position = "absolute";
          }
        }
      });
    });

    // Ferme au clic en dehors
    document.addEventListener("click", function (e) {
      if (!e.target.closest(".dropdown") && !e.target.closest("#profile-btn")) {
        closeAllDropdowns();
      }
    });

    // Ferme à l'Escape
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        closeAllDropdowns();
        closeProfileDropdown();
      }
    });

    function closeAllDropdowns() {
      document.querySelectorAll(".dropdown.open").forEach((d) => {
        d.classList.remove("open");
      });
    }

    // ===== DROPDOWN PROFIL =====
    const profileBtn = document.getElementById("profile-btn");
    const dropdownMenu = document.getElementById("dropdown-menu");

    profileBtn?.addEventListener("click", function (e) {
      e.stopPropagation();
      const isOpen = dropdownMenu.classList.contains("show");

      // Ferme les autres dropdowns
      closeAllDropdowns();

      if (isOpen) {
        closeProfileDropdown();
      } else {
        dropdownMenu.classList.add("show");
        profileBtn.classList.add("active");
        // Force z-index
        dropdownMenu.style.zIndex = "99999";
        dropdownMenu.style.position = "absolute";
      }
    });

    document.addEventListener("click", function (e) {
      if (!e.target.closest("#profile-btn")) {
        closeProfileDropdown();
      }
    });

    function closeProfileDropdown() {
      dropdownMenu?.classList.remove("show");
      profileBtn?.classList.remove("active");
    }

    // ===== RIPPLE =====
    document.querySelectorAll(".dropdown-content-now li a").forEach((link) => {
      link.addEventListener("click", function (e) {
        const rect = this.getBoundingClientRect();
        const ripple = document.createElement("span");
        ripple.classList.add("ripple");
        ripple.style.left = e.clientX - rect.left + "px";
        ripple.style.top = e.clientY - rect.top + "px";
        this.appendChild(ripple);
        setTimeout(() => ripple.remove(), 600);
      });
    });

    // ===== SCROLL HEADER =====
    const siteHeader = document.querySelector(".main-header");
    if (siteHeader) {
      window.addEventListener(
        "scroll",
        function () {
          siteHeader.classList.toggle("scrolled", window.scrollY > 50);
        },
        { passive: true },
      );
    }

    // ===== MENU MOBILE =====
    const hamburger = document.querySelector(".hamburger");
    const mobileMenu = document.getElementById("mobile-menu");
    const overlay = document.querySelector(".overlay");
    const dropdownMobiles = document.querySelectorAll(
      ".dropdown-mobile > .dropbtn-mobile",
    );

    hamburger?.addEventListener("click", function () {
      const expanded = this.getAttribute("aria-expanded") === "true";
      this.setAttribute("aria-expanded", String(!expanded));
      mobileMenu?.classList.toggle("open");
      overlay?.classList.toggle("active");
      document.body.style.overflow = expanded ? "" : "hidden";
    });

    overlay?.addEventListener("click", function () {
      hamburger?.setAttribute("aria-expanded", "false");
      mobileMenu?.classList.remove("open");
      overlay.classList.remove("active");
      document.body.style.overflow = "";
    });

    dropdownMobiles.forEach((btn) => {
      btn.addEventListener("click", function (e) {
        e.preventDefault();
        const parent = this.closest(".dropdown-mobile");
        const isOpen = parent.classList.contains("open");

        // Ferme les autres
        document.querySelectorAll(".dropdown-mobile.open").forEach((d) => {
          if (d !== parent) d.classList.remove("open");
        });

        parent.classList.toggle("open", !isOpen);
      });
    });
  });
})();
