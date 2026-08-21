document.addEventListener('DOMContentLoaded', () => {
  const root = document.createElement('div');
  root.className = 'toast-root';
  root.setAttribute('aria-live', 'polite');
  document.body.appendChild(root);

  const toast = (message, icon = 'fa-check-circle') => {
    const item = document.createElement('div');
    item.className = 'ui-toast';
    item.innerHTML = `<i class="fas ${icon}"></i><span>${message}</span><button type="button" aria-label="Dismiss"><i class="fas fa-times"></i></button>`;
    item.querySelector('button').addEventListener('click', () => item.remove());
    root.appendChild(item);
    requestAnimationFrame(() => item.classList.add('show'));
    setTimeout(() => { item.classList.remove('show'); setTimeout(() => item.remove(), 250); }, 3200);
  };

  document.querySelectorAll('[data-toast]').forEach((el) => el.addEventListener('click', () => toast(el.dataset.toast, el.dataset.icon || 'fa-check-circle')));

  document.querySelectorAll('[data-like]').forEach((button) => button.addEventListener('click', () => {
    const active = button.classList.toggle('is-liked');
    button.setAttribute('aria-pressed', String(active));
    const icon = button.querySelector('i');
    if (icon) icon.className = active ? 'fas fa-heart' : 'far fa-heart';
    button.animate([{transform:'scale(1)'},{transform:'scale(1.2)'},{transform:'scale(1)'}], {duration:260,easing:'ease-out'});
    toast(active ? 'Story added to your likes.' : 'Story removed from your likes.', active ? 'fa-heart' : 'fa-heart-broken');
  }));

  document.querySelectorAll('[data-follow]').forEach((button) => button.addEventListener('click', () => {
    const following = button.classList.toggle('is-following');
    button.textContent = following ? 'Following' : 'Follow';
    button.setAttribute('aria-pressed', String(following));
    toast(following ? 'You are now following this writer.' : 'You unfollowed this writer.', following ? 'fa-user-check' : 'fa-user');
  }));

  document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(button.dataset.copy || window.location.href); toast('Link copied to clipboard.', 'fa-link'); }
    catch (_) { toast('Could not copy the link.', 'fa-exclamation-circle'); }
  }));

  document.querySelectorAll('.story-card, .feature-card, .featured-card').forEach((card) => {
    card.addEventListener('mouseenter', () => card.classList.add('is-hovered'));
    card.addEventListener('mouseleave', () => card.classList.remove('is-hovered'));
  });

  document.querySelectorAll('[data-reveal]').forEach((element) => {
    const observer = new IntersectionObserver((entries, obs) => entries.forEach((entry) => {
      if (entry.isIntersecting) { entry.target.classList.add('revealed'); obs.unobserve(entry.target); }
    }), {threshold:0.12});
    observer.observe(element);
  });

  window.WeblogrUI = { toast };
});
