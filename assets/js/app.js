// GameVault — global JS
document.addEventListener('DOMContentLoaded', () => {
  const t = document.getElementById('navToggle');
  const l = document.getElementById('navLinks');
  if (t && l) t.addEventListener('click', () => l.classList.toggle('open'));

  // Favorite toggle (visual)
  document.querySelectorAll('.fav-btn').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      btn.classList.toggle('active');
      const id = btn.dataset.id;
      if (id) fetch('favorites.php?toggle=' + id, {credentials:'same-origin'});
    });
  });
});

