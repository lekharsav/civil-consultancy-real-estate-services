
    // 1) ORDER TRACK PATH: animate the "moving" group along SVG path
    (function () {
      const path = document.getElementById('trackPath');
      const moving = document.getElementById('moving');

      if (path && moving) {
        const pathLen = path.getTotalLength();
        let start = null;
        let duration = 7000; // ms to complete one pass
        // ping-pong effect and sync steps highlight
        let direction = 1;

        function step(ts) {
          if (!start) start = ts;
          let t = (ts - start) % duration;
          let progress = t / duration;
          // ping-pong progress
          let p = direction === 1 ? progress : 1 - progress;
          let pointAt = path.getPointAtLength(p * pathLen);

          moving.setAttribute('transform', `translate(${pointAt.x}, ${pointAt.y - 10})`);
          // pulse active step based on progress
          const steps = document.querySelectorAll('.track-steps .step');
          steps.forEach((s, i) => s.classList.remove('active'));
          let idx = Math.min(steps.length - 1, Math.floor(p * steps.length));
          steps[idx].classList.add('active');

          // at ends reverse direction
          if ((progress >= 0.999 && direction === 1) || (progress >= 0.999 && direction === -1)) {
            direction *= -1;
          }
          requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
      }
    })();

    // 2) Floating shapes: small parallax on mouse move
    (function () {
      const shapes = document.querySelectorAll('.floating-shapes .shape');
      if (!shapes.length) return;
      document.addEventListener('mousemove', (e) => {
        const cx = window.innerWidth / 2;
        const cy = window.innerHeight / 2;
        const dx = (e.clientX - cx) / cx;
        const dy = (e.clientY - cy) / cy;
        shapes.forEach((el, i) => {
          const depth = (i + 1) * 6;
          el.style.transform = `translate3d(${dx * depth}px, ${dy * depth}px, 0) rotate(${dx * depth}deg)`;
        });
      });
    })();

    // 3) Tabs filter for courses (simple)
    (function () {
      const tabs = document.querySelectorAll('.courses .tab');
      const cards = document.querySelectorAll('.course-card');
      tabs.forEach(tab => {
        tab.addEventListener('click', () => {
          tabs.forEach(t => t.classList.remove('active'));
          tab.classList.add('active');
          const cat = tab.dataset.cat;
          cards.forEach(c => {
            if (cat === 'all' || c.dataset.cat === cat) {
              c.style.display = 'block';
            } else {
              c.style.display = 'none';
            }
          });
          // small layout adjustment
          setTimeout(()=> window.dispatchEvent(new Event('resize')), 350);
        });
      });
    })();

    // 4) FAQ accordion behavior



    
    // 5) small reveal animations for sections (on-scroll)
    (function () {
      // Only apply reveal transforms to sections that do NOT contain modal markup.
      const all = Array.from(document.querySelectorAll('section, .course-card, .testimonial'));
      const sections = all.filter(el => !el.querySelector('.modal'));
      if(!sections.length) return;
      const io = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.transform = 'translateY(0)'; entry.target.style.opacity = 1;
            entry.target.style.transition = 'all 700ms cubic-bezier(.2,.9,.2,1)';
          } else {
            entry.target.style.transform = 'translateY(6px)'; entry.target.style.opacity = 0.98;
          }
        });
      }, {threshold: 0.08});
      sections.forEach(s => { s.style.transform = 'translateY(6px)'; s.style.opacity = 0.98; io.observe(s); });
    })();

    // 6) keyboard focus outline improvements for accessibility
    (function () {
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') document.documentElement.classList.add('show-focus');
      });
      document.addEventListener('mousedown', () => document.documentElement.classList.remove('show-focus'));
    })();

    // 7) micro interaction: CTA scroll (guard elements)
    (function(){
      const cta = document.getElementById('ctaEnroll');
      if(!cta) return;
      cta.addEventListener('click', function () {
        const coursesEl = document.getElementById('courses');
        if(coursesEl) coursesEl.scrollIntoView({behavior: 'smooth'});
      });
    })();

    // 8) ensure moving dot reposition on resize for SVG scaling
    window.addEventListener('resize', () => {
      // noop for now - path animation uses getPointAtLength which adapts to SVG scaling
    });

  // Profile/login overlay: attach handlers after DOM ready and guard against missing elements
  document.addEventListener('DOMContentLoaded', function(){
    const profileBtn = document.getElementById('profileBtn');
    const authOverlay = document.getElementById('authOverlay');
    if(!profileBtn || !authOverlay) return;

    profileBtn.addEventListener('click', () => {
      authOverlay.classList.add('active');
    });

    authOverlay.addEventListener('click', (e) => {
      if (e.target === authOverlay) {
        authOverlay.classList.remove('active');
      }
    });

    // open bootstrap login modal when login button inside overlay is clicked
    const loginBtn = authOverlay.querySelector('.auth-main.login');
    const loginModalEl = document.getElementById('loginModal');
    if(loginBtn && loginModalEl){
      loginBtn.addEventListener('click', ()=>{
        authOverlay.classList.remove('active');
        const m = new bootstrap.Modal(loginModalEl);
        m.show();
      });
    }
  });
// 9) Hero entry animation trigger
(function () {
  const hero = document.querySelector('.hero');
  if (!hero) return;

  // Trigger after first paint
  requestAnimationFrame(() => {
    hero.classList.add('animate');
  });
})();

/* =====================================================
   COURSE PAGINATION (RESPONSIVE)
   ===================================================== */

(function () {
  const allCards = Array.from(document.querySelectorAll('.course-card'));
  const prevBtn = document.getElementById('prevPage');
  const nextBtn = document.getElementById('nextPage');
  const pageInfo = document.getElementById('pageInfo');

  let currentPage = 1;
  let activeCards = [...allCards];

  function cardsPerPage() {
    return window.innerWidth <= 480 ? 3 : 6;
  }

  function render() {
    const perPage = cardsPerPage();
    const totalPages = Math.ceil(activeCards.length / perPage) || 1;

    activeCards.forEach((card, index) => {
      card.style.display =
        index >= (currentPage - 1) * perPage &&
        index < currentPage * perPage
          ? 'block'
          : 'none';
    });

    pageInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    prevBtn.disabled = currentPage === 1;
    nextBtn.disabled = currentPage === totalPages;
  }

  function applyFilter(category) {
    activeCards = allCards.filter(card =>
      category === 'all' || card.dataset.cat === category
    );
    currentPage = 1;
    render();
  }

  prevBtn.addEventListener('click', () => {
    if (currentPage > 1) {
      currentPage--;
      render();
    }
  });

  nextBtn.addEventListener('click', () => {
    const totalPages = Math.ceil(activeCards.length / cardsPerPage());
    if (currentPage < totalPages) {
      currentPage++;
      render();
    }
  });

  /* Hook into your existing tabs */
  document.querySelectorAll('.courses .tab').forEach(tab => {
    tab.addEventListener('click', () => {
      applyFilter(tab.dataset.cat);
    });
  });

  window.addEventListener('resize', () => {
    currentPage = 1;
    render();
  });

  render();
})();

 


function openFaqModal(title, body) {
  document.getElementById('faqModalTitle').innerText = title;
  document.getElementById('faqModalBody').innerText = body;

  const modal = new bootstrap.Modal(document.getElementById('faqModal'));
  modal.show();
}


function openFaqModal(title, body) {
  const modalEl = document.getElementById('faqModal');
  const modalTitle = document.getElementById('faqModalTitle');
  const modalBody = document.getElementById('faqModalBody');

  modalTitle.textContent = title;
  modalBody.textContent = body;

  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
  modal.show();
}

// Ensure any modal markup is moved to document.body so fixed positioning and Bootstrap backdrop work correctly
document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.modal').forEach(m => {
    if(m.parentElement !== document.body) document.body.appendChild(m);
  });
});
