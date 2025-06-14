document.addEventListener("DOMContentLoaded", function () {
  console.log("Zaltix website loaded");

  // ✅ SLIDER FUNCTIONALITY
  const leftArrow = document.getElementById("left-arrow");
  const rightArrow = document.getElementById("right-arrow");
  const projectSlider = document.getElementById("project-slider");

  const imagesPerSlide = 3; // Number of images per slide
  const totalImages = 9; // Total number of images in your slider
  let currentIndex = 0;

  function slideLeft() {
    if (currentIndex > 0) {
      currentIndex -= imagesPerSlide;
      projectSlider.style.transform = `translateX(-${(currentIndex / totalImages) * 100}%)`;
    }
  }

  function slideRight() {
    if (currentIndex < totalImages - imagesPerSlide) {
      currentIndex += imagesPerSlide;
      projectSlider.style.transform = `translateX(-${(currentIndex / totalImages) * 100}%)`;
    }
  }

  if (leftArrow && rightArrow && projectSlider) {
    leftArrow.addEventListener("click", slideLeft);
    rightArrow.addEventListener("click", slideRight);
  }

  // ✅ READ MORE TOGGLE FUNCTIONALITY
  document.querySelectorAll('.read-more-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const moreText = btn.previousElementSibling?.querySelector('.more-text');
      if (moreText) {
        const isVisible = moreText.style.display === 'inline';
        moreText.style.display = isVisible ? 'none' : 'inline';
        btn.textContent = isVisible ? 'Read More' : 'Show Less';
      }
    });
  });

  // ✅ FORM SUBMISSION CONFIRMATION FUNCTIONALITY
  const contactForm = document.getElementById("contact-form");

  if (contactForm) {
    contactForm.addEventListener("submit", function (event) {
      event.preventDefault();
      alert("Message sent successfully!");
      contactForm.reset();
    });
  }
});
