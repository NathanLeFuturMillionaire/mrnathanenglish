function toggleModule(header) {
  const card = header.closest(".module-card");
  const body = card.querySelector(".module-card__body");

  if (!body) return;

  const isOpen = body.classList.contains("is-open");

  body.classList.toggle("is-open", !isOpen);
  header.classList.toggle("is-open", !isOpen);
}

// Ouvre le premier module par défaut
document.addEventListener("DOMContentLoaded", () => {
  const first = document.querySelector(".module-card__header");
  if (first) toggleModule(first);
});

const sticky = document.querySelector(".payment-sticky");
const btnFollow = document.querySelector(".btn-follow");

if (sticky && btnFollow) {
  const observer = new IntersectionObserver(
    ([entry]) => {
      sticky.classList.toggle("is-visible", !entry.isIntersecting);
    },
    { threshold: 0 },
  );

  observer.observe(btnFollow);
}
