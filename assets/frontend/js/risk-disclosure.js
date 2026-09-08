(function () {
  "use strict";

  function getRoot(el) {
    if (!el || !el.closest) return null;
    return el.closest(".cp-risk-disclosure");
  }

  function setBtnState(btn, ok) {
    if (!btn) return;
    btn.disabled = !ok;
    btn.setAttribute("aria-disabled", ok ? "false" : "true");
    btn.style.background = ok ? "#16a34a" : "#d1d5db";
    btn.style.color = ok ? "#ffffff" : "#6b7280";
    btn.style.cursor = ok ? "pointer" : "not-allowed";
  }

  function validate(root) {
    if (!root) return;
    var check = root.querySelector("[data-cp-risk-check]");
    var input = root.querySelector("[data-cp-risk-input]");
    var btn = root.querySelector("[data-cp-risk-button]");
    var hint = root.querySelector("[data-cp-risk-hint]");
    if (!check || !input || !btn) return;

    var required = (
      root.getAttribute("data-required-phrase") || "SAYA PAHAM RISIKO TRADING"
    ).trim();
    var ok = !!check.checked && (input.value || "").trim() === required;
    setBtnState(btn, ok);

    if (hint) {
      if (ok) {
        hint.textContent = "Siap. Silakan klik tombol untuk ambil lisensi.";
        hint.style.color = "#16a34a";
      } else {
        hint.textContent =
          "Tombol aktif hanya jika kotak dicentang DAN teks sama persis (huruf besar/kecil & spasi harus sama).";
        hint.style.color = "#6b7280";
      }
    }
  }

  function validateAll() {
    var nodes = document.querySelectorAll(".cp-risk-disclosure");
    for (var i = 0; i < nodes.length; i++) validate(nodes[i]);
  }

  // Delegasi di document — tetap jalan walau form dimuat via AJAX / Elementor.
  document.addEventListener(
    "input",
    function (e) {
      if (
        e.target &&
        e.target.matches &&
        e.target.matches("[data-cp-risk-input]")
      ) {
        validate(getRoot(e.target));
      }
    },
    true,
  );

  document.addEventListener(
    "change",
    function (e) {
      if (
        e.target &&
        e.target.matches &&
        (e.target.matches("[data-cp-risk-input]") ||
          e.target.matches("[data-cp-risk-check]"))
      ) {
        validate(getRoot(e.target));
      }
    },
    true,
  );

  document.addEventListener(
    "keyup",
    function (e) {
      if (
        e.target &&
        e.target.matches &&
        e.target.matches("[data-cp-risk-input]")
      ) {
        validate(getRoot(e.target));
      }
    },
    true,
  );

  document.addEventListener(
    "click",
    function (e) {
      var t = e.target;
      if (!t || !t.closest) return;

      var check = t.closest("[data-cp-risk-check]");
      if (check) {
        setTimeout(function () {
          validate(getRoot(check));
        }, 0);
        return;
      }

      // Klik pada label teks ikut toggle checkbox secara native,
      // fallback validasi setelah toggle.
      var label = t.closest(".cp-risk-disclosure label");
      if (label && !t.closest("[data-cp-risk-button]")) {
        setTimeout(validateAll, 0);
      }

      var btn = t.closest("[data-cp-risk-button]");
      if (btn) {
        var root = getRoot(btn);
        validate(root);
        if (btn.disabled) {
          e.preventDefault();
          return;
        }

        // 1) URL custom di shortcode menang duluan (jika diisi).
        var url = root
          ? (root.getAttribute("data-button-url") || "").trim()
          : "";
        if (url) {
          window.location.href = url;
          return;
        }

        // 2) Default: kirim konfirmasi ke WhatsApp admin (nomor dari setting CF7).
        var wa = root ? (root.getAttribute("data-wa-number") || "").trim() : "";
        if (wa) {
          var msg =
            "Halo Tradershood Community, saya sudah membaca & memahami risiko trading." +
            "\n\nSAYA PAHAM RISIKO TRADING." +
            "\n\nSaya ingin mengambil lisensi software Expert Advisor (EA)." +
            "\nMohon info langkah selanjutnya. Terima kasih.";
          var waUrl =
            "https://wa.me/" + wa + "?text=" + encodeURIComponent(msg);
          window.open(waUrl, "_blank");
          return;
        }

        // 3) Tidak ada URL & nomor kosong: cukup kirim event.
        if (root)
          root.dispatchEvent(
            new CustomEvent("cp:risk-accepted", { bubbles: true }),
          );
      }
    },
    true,
  );

  function boot() {
    validateAll();
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
  } else {
    boot();
  }
})();
