// Marketplace client helpers
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('filterForm');
  if (form) {
    form.querySelectorAll('select, input[type=range]').forEach(el => {
      el.addEventListener('change', () => form.submit());
    });
  }
});

