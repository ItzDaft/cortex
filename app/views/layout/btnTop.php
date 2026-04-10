
<button  id="backToTopButton" class="btn btn-primary position-fixed d-none rounded-circle shadow-lg border border-2 border-white" style="bottom: 20px; right: 20px; width: 56px; height: 56px; z-index: 1050;" aria-label="Volver al inicio de la página" >
    <i class="bi bi-arrow-up-short fs-3"></i>
</button>

<script>

const backToTopButton = document.getElementById("backToTopButton");

window.addEventListener("scroll", function() {
    if (window.scrollY > 300) {
        backToTopButton.classList.remove("d-none", "opacity-0");
        backToTopButton.classList.add("opacity-100");
    } else {
        backToTopButton.classList.remove("opacity-100");
        backToTopButton.classList.add("opacity-0");
        setTimeout(() => {
            if (window.scrollY <= 300) {
                backToTopButton.classList.add("d-none");
            }
        }, 300);
    }
});
    
backToTopButton.addEventListener("click", function() {
    window.scrollTo({ top: 0, behavior: "smooth" });
});

</script>