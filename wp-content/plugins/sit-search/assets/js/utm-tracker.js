/**
 * SIT UTM Tracker — Site-Wide Attribution Persistence
 *
 * Captures UTM parameters from the URL on any page, persists them
 * across page navigations via cookie + localStorage, and auto-injects
 * them into every POST form on the site.
 *
 * Attribution model: Last-touch with UTM. If a visitor arrives with
 * new UTM params, previous attribution is overwritten.
 *
 * @since 1.4.0
 */
(function () {
  'use strict';

  var UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
  var STORAGE_KEY = 'sit_lead_tracking';
  var COOKIE_NAME = 'sit_utm';
  var EXPIRY_DAYS = 30;

  // ── Cookie helpers ──
  function setCookie(name, value, days) {
    var d = new Date();
    d.setTime(d.getTime() + days * 86400000);
    document.cookie = name + '=' + encodeURIComponent(value) +
      ';expires=' + d.toUTCString() +
      ';path=/;SameSite=Lax';
  }

  function getCookie(name) {
    var match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
    return match ? decodeURIComponent(match[2]) : null;
  }

  // ── Storage helpers (cookie + localStorage for redundancy) ──
  function readStore() {
    try {
      // Try localStorage first
      var ls = localStorage.getItem(STORAGE_KEY);
      if (ls) return JSON.parse(ls);

      // Fallback to cookie
      var ck = getCookie(COOKIE_NAME);
      if (ck) return JSON.parse(ck);
    } catch (e) { /* ignore */ }
    return {};
  }

  function writeStore(data) {
    var json = JSON.stringify(data);
    try { localStorage.setItem(STORAGE_KEY, json); } catch (e) { /* ignore */ }
    setCookie(COOKIE_NAME, json, EXPIRY_DAYS);
  }

  // ── URL param reader ──
  function getParam(name) {
    try {
      var url = new URL(window.location.href);
      return (url.searchParams.get(name) || '').trim();
    } catch (e) { return ''; }
  }

  // ── 1. Capture from URL ──
  var currentUtm = {};
  var hasNewUtm = false;

  UTM_KEYS.forEach(function (key) {
    var val = getParam(key);
    if (val) {
      currentUtm[key] = val;
      hasNewUtm = true;
    }
  });

  // ── 2. Check expiry ──
  var saved = readStore();
  var now = Date.now();
  if (saved.saved_at && (now - saved.saved_at) > EXPIRY_DAYS * 86400000) {
    saved = {}; // expired
  }

  // ── 3. Merge: new UTMs overwrite old (last-touch with UTM) ──
  if (hasNewUtm) {
    // Overwrite UTMs but keep other tracking data
    UTM_KEYS.forEach(function (key) {
      if (currentUtm[key]) saved[key] = currentUtm[key];
    });
    saved.landing_page = window.location.href.split('#')[0];
    saved.referrer = document.referrer || '';
    saved.saved_at = now;
    writeStore(saved);
  } else if (!saved.saved_at) {
    // First visit without UTM — capture referrer at least
    saved.landing_page = window.location.href.split('#')[0];
    saved.referrer = document.referrer || '';
    saved.saved_at = now;
    writeStore(saved);
  }

  // ── 4. Auto-inject into forms ──
  var tracking = readStore();

  function injectIntoForm(form) {
    // Skip GET forms (search forms)
    if (form.method && form.method.toLowerCase() === 'get') return;
    // Skip admin forms
    if (form.action && form.action.indexOf('options.php') > -1) return;

    var fields = {
      'utm_source': tracking.utm_source || '',
      'utm_medium': tracking.utm_medium || '',
      'utm_campaign': tracking.utm_campaign || '',
      'utm_content': tracking.utm_content || '',
      'utm_term': tracking.utm_term || '',
      'landing_page': tracking.landing_page || '',
      'referrer': tracking.referrer || ''
    };

    Object.keys(fields).forEach(function (name) {
      // Check if hidden input already exists
      var existing = form.querySelector('input[name="' + name + '"]');
      if (existing) {
        // Only fill if empty (don't overwrite user-set values)
        if (!existing.value) existing.value = fields[name];
      } else {
        // Create new hidden input
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = fields[name];
        form.appendChild(input);
      }
    });
  }

  // Inject on DOMContentLoaded
  function injectAll() {
    var forms = document.querySelectorAll('form');
    for (var i = 0; i < forms.length; i++) {
      injectIntoForm(forms[i]);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', injectAll);
  } else {
    injectAll();
  }

  // Also watch for dynamically added forms (e.g., modals, popups)
  if (typeof MutationObserver !== 'undefined') {
    var observer = new MutationObserver(function (mutations) {
      mutations.forEach(function (mutation) {
        mutation.addedNodes.forEach(function (node) {
          if (node.nodeName === 'FORM') {
            injectIntoForm(node);
          }
          if (node.querySelectorAll) {
            var innerForms = node.querySelectorAll('form');
            for (var i = 0; i < innerForms.length; i++) {
              injectIntoForm(innerForms[i]);
            }
          }
        });
      });
    });
    observer.observe(document.body || document.documentElement, { childList: true, subtree: true });
  }
})();
