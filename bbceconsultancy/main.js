// Cursor follow dot
const dot = document.querySelector('.cursor-dot');

document.addEventListener('mousemove', e => {
  dot.style.left = e.clientX + 'px';
  dot.style.top = e.clientY + 'px';
});

// Simple scroll reveal
const reveals = document.querySelectorAll('.glass');

const io = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.transform = 'translateY(0)';
      entry.target.style.opacity = '1';
    }
  });
});

reveals.forEach(el => {
  el.style.transform = 'translateY(20px)';
  el.style.opacity = '0';
  el.style.transition = '0.6s ease';
  io.observe(el);
});
