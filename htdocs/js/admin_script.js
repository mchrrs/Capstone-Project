// Select elements
const menuBtn = document.getElementById('menu-btn');
const sidebar = document.querySelector('.sidebar');

// Toggle sidebar visibility
menuBtn.addEventListener('click', () => {
   sidebar.classList.toggle('hidden');
});
