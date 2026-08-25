<link rel="stylesheet" href="<?= asset_css('scroll-top.css') ?>">

<a href="#" class="scroll-top" id="scroll-top">
  <img src="<?= IMGPATH ?>/scroll-up.webp" alt="Back to Top" class="back-to-top-btn" title="Back to top">
  <!-- <span>Back to Top</span> -->
</a>

<script>
  window.addEventListener("scroll", () => {
    const scrollTop = document.getElementById("scroll-top");
    if (window.scrollY > 200) {
      scrollTop.classList.add("is-visible");
    } else {
      scrollTop.classList.remove("is-visible");
    }
  });
</script>