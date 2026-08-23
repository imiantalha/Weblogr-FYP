document.addEventListener('DOMContentLoaded', () => {
  const root = document.createElement('div');
  root.className = 'toast-root';
  root.setAttribute('aria-live', 'polite');
  root.setAttribute('aria-atomic', 'true');
  document.body.appendChild(root);

  const toast = (message, icon = 'fa-check-circle') => {
    const item = document.createElement('div');
    item.className = 'ui-toast';

    const iconEl = document.createElement('i');
    iconEl.className = `fas ${icon}`;
    iconEl.setAttribute('aria-hidden', 'true');

    const textEl = document.createElement('span');
    textEl.textContent = message;

    const close = document.createElement('button');
    close.type = 'button';
    close.setAttribute('aria-label', 'Dismiss notification');
    close.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i>';
    close.addEventListener('click', () => item.remove());

    item.append(iconEl, textEl, close);
    root.appendChild(item);
    requestAnimationFrame(() => item.classList.add('show'));

    window.setTimeout(() => {
      item.classList.remove('show');
      window.setTimeout(() => item.remove(), 250);
    }, 3200);
  };

  document.querySelectorAll('[data-toast]').forEach((el) => {
    el.addEventListener('click', () => toast(el.dataset.toast || 'Done', el.dataset.icon || 'fa-check-circle'));
  });

  document.querySelectorAll('[data-copy]').forEach((button) => {
    button.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(button.dataset.copy || window.location.href);
        toast('Link copied to clipboard.', 'fa-link');
      } catch (_) {
        toast('Could not copy the link.', 'fa-exclamation-circle');
      }
    });
  });

  document.querySelectorAll('.story-card, .feature-card, .featured-card').forEach((card) => {
    card.addEventListener('mouseenter', () => card.classList.add('is-hovered'));
    card.addEventListener('mouseleave', () => card.classList.remove('is-hovered'));
  });

  document.querySelectorAll('[data-reveal]').forEach((element) => {
    if (!('IntersectionObserver' in window)) {
      element.classList.add('revealed');
      return;
    }

    const observer = new IntersectionObserver((entries, obs) => entries.forEach((entry) => {
      if (entry.isIntersecting) {
        entry.target.classList.add('revealed');
        obs.unobserve(entry.target);
      }
    }), { threshold: 0.12 });
    observer.observe(element);
  });

  // Generic product UI helpers. These only update the interface; server-backed
  // actions should continue to be handled by their existing form/endpoints.
  document.querySelectorAll('[data-ui-toggle]').forEach((button) => {
    const target = document.querySelector(button.dataset.uiToggle);
    if (!target) return;
    button.addEventListener('click', () => {
      const expanded = target.classList.toggle('is-open');
      button.setAttribute('aria-expanded', String(expanded));
    });
  });

  window.WeblogrUI = { toast };
});
