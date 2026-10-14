/**
 * Smart Store Credit and Customer Dues Management System
 * Core Frontend Interactions & Theme State Controller
 */

(function initTheme() {
  const currentTheme = localStorage.getItem('smart_store_theme') || 'light';
  document.documentElement.setAttribute('data-theme', currentTheme);
  updateThemeIcon(currentTheme);
})();

function toggleTheme() {
  const activeTheme = document.documentElement.getAttribute('data-theme') || 'light';
  const newTheme = (activeTheme === 'light') ? 'dark' : 'light';
  
  document.documentElement.setAttribute('data-theme', newTheme);
  localStorage.setItem('smart_store_theme', newTheme);
  updateThemeIcon(newTheme);
}

function updateThemeIcon(theme) {
  const icon = document.getElementById('themeToggleIcon');
  if (icon) {
    if (theme === 'dark') {
      icon.className = 'ri-sun-line';
      icon.title = 'Switch to Light Mode';
    } else {
      icon.className = 'ri-moon-line';
      icon.title = 'Switch to Dark Mode';
    }
  }
}

const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');

if (menuToggle && sidebar) {
  menuToggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
  });

  document.addEventListener('click', (e) => {
    if (!sidebar.contains(e.target) && !menuToggle.contains(e.target) && sidebar.classList.contains('open')) {
      sidebar.classList.remove('open');
    }
  });
}

const searchInput = document.getElementById('searchInput');
if (searchInput) {
  searchInput.addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.customer-row');
    let visibleCount = 0;

    rows.forEach(row => {
      const name = row.querySelector('.cust-name').innerText.toLowerCase();
      const phone = row.querySelector('.cust-phone').innerText.toLowerCase();
      const address = row.querySelector('.cust-address').innerText.toLowerCase();

      if (name.includes(query) || phone.includes(query) || address.includes(query)) {
        row.style.display = '';
        visibleCount++;
      } else {
        row.style.display = 'none';
      }
    });

    const countBadge = document.getElementById('customerCount');
    if (countBadge) countBadge.innerText = visibleCount;
  });
}
