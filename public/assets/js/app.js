// Small progressive enhancements. The site works without this file except the phone menu.
document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.querySelector('[data-menu-toggle]');
  const nav = document.getElementById('mobile-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => {
      const open = nav.classList.toggle('hidden') === false;
      nav.classList.toggle('flex', open);
      toggle.setAttribute('aria-expanded', String(open));
    });
  }

  // Product photos on phones: keep the dots under the swipe gallery in step.
  const gallery = document.querySelector('[data-gallery]');
  const dots = document.querySelectorAll('[data-dot]');
  if (gallery && dots.length) gallery.addEventListener('scroll', () => {
    const i = Math.round(gallery.scrollLeft / gallery.clientWidth);
    dots.forEach((d, n) => { d.classList.toggle('bg-black', n === i); d.classList.toggle('bg-neutral-300', n !== i); });
  }, { passive: true });

  // Confirm destructive admin actions.
  document.querySelectorAll('form[data-confirm]').forEach((f) => {
    f.addEventListener('submit', (e) => {
      if (!window.confirm(f.dataset.confirm)) e.preventDefault();
    });
  });

  // Confirm buttons that sit inside a larger form, such as removing a photo.
  document.querySelectorAll('[data-confirm-btn]').forEach((b) => {
    b.addEventListener('click', (e) => {
      if (!window.confirm(b.dataset.confirmBtn)) e.preventDefault();
    });
  });

  // Submit filter forms as soon as a select changes.
  document.querySelectorAll('[data-autosubmit]').forEach((el) => {
    el.addEventListener('change', () => el.form && el.form.submit());
  });

  // Format card number and expiry while typing.
  const num = document.querySelector('[data-card-number]');
  if (num) num.addEventListener('input', () => {
    num.value = num.value.replace(/\D/g, '').slice(0, 19).replace(/(.{4})/g, '$1 ').trim();
  });
  const exp = document.querySelector('[data-card-expiry]');
  if (exp) exp.addEventListener('input', () => {
    const d = exp.value.replace(/\D/g, '').slice(0, 4);
    exp.value = d.length > 2 ? d.slice(0, 2) + ' / ' + d.slice(2) : d;
  });
});
