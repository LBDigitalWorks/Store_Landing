<script>
// assets/js/i18n.js
(function () {
  const DICT = {
    en: {
      greeting: 'Hey {name}!',
      // nav
      nav_order_now: 'Order Now',
      nav_about: 'About',
      nav_reorder: 'Reorder',
      nav_account: 'Account',
      // basket
      basket: 'Basket',
      delivery: 'Delivery',
      collection: 'Collection',
      available: '(available)',
      subtotal: 'Subtotal',
      delivery_fee: 'Delivery Fee',
      total: 'Total',
      checkout: 'Checkout',
      // options
      choose_one: 'Choose one',
      required: '(required)',
      add_extras: 'Add extras',
      optional: '(optional)',
      customise_and_qty: 'Customise your {name} and set quantity',
      add: 'Add',
      // generic UI used on index/about/etc
      recommended_for_you: 'RECOMMENDED FOR YOU',
      swipe: 'Swipe',
      open_now: 'We\'re open now – place your order!',
      closed_now: 'We\'re closed at the moment. Please check back during our opening hours.',
    },
    pl: { /* …fill like before… */ },
    ro: { /* … */ },
    ar: { /* … */ },
    tr: { /* … */ },
    uk: { /* … */ }
  };

  function getLang() { return localStorage.getItem('site_lang') || 'en'; }

  function t(key, lang, vars = {}) {
    const L = DICT[lang] || DICT.en;
    let s = L[key] ?? DICT.en[key] ?? key;
    for (const k in vars) s = s.replace(new RegExp('\\{' + k + '\\}', 'g'), vars[k]);
    return s;
  }

  function applyI18n(lang) {
    document.documentElement.lang = lang;
    document.documentElement.dir  = (lang === 'ar') ? 'rtl' : 'ltr';

    document.querySelectorAll('[data-i18n]').forEach(el => {
      const key  = el.getAttribute('data-i18n');
      const vars = el.getAttribute('data-i18n-vars');
      let v = {};
      if (vars) { try { v = JSON.parse(vars); } catch(e){} }

      const txt = t(key, lang, v);

      // If element has a placeholder, translate that; else use textContent
      if ('placeholder' in el && el.tagName === 'INPUT') el.placeholder = txt;
      else el.textContent = txt;
    });

    // Let modules (basket/options/etc) react and update dynamic labels
    document.dispatchEvent(new CustomEvent('lang:applied', { detail: { lang } }));
  }

  function setLanguage(lang) {
    localStorage.setItem('site_lang', lang);
    applyI18n(lang);
  }

  // Expose small API
  window.I18N = { t, applyI18n, setLanguage, getLang };

  // Auto-apply on every page
  document.addEventListener('DOMContentLoaded', () => applyI18n(getLang()));
})();
</script>
