document.addEventListener("DOMContentLoaded", () => {
    const receptButtons = document.querySelectorAll(".recept-button");

    receptButtons.forEach(button => {
        button.addEventListener("click", () => {
            const link = button.getAttribute("data-link");
            window.location.href = link;
        });
    });
});
