var buttons = document.querySelectorAll(".toggle-details");

buttons.forEach(button => {
    button.onclick = function (event) {
        event.preventDefault();
        window.location.href = this.getAttribute("href");
    };
});
