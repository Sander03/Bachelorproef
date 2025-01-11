var servingsLabel = document.getElementById("porties-aantal");
var decreaseButton = document.getElementById("verminder-porties");
var increaseButton = document.getElementById("vermeerder-porties");
var ingredientItems = document.querySelectorAll(".ingredient-item");
var huidigeEcoscore = document.getElementById("huidige-ecoscore");
var maxPosEcoscore = document.getElementById("max-ecoscore");
var currentServings = parseInt(servingsLabel.innerText.split(" ")[0]);

var origineleEcoscore = parseInt(huidigeEcoscore.innerText.split(": ")[1]);
var maxScore = parseInt(maxPosEcoscore.innerText);

var updateServings = (newServings) => {
    if (newServings < 1) return;

    currentServings = newServings;
    servingsLabel.textContent = `${currentServings} personen`;

    ingredientItems.forEach((item) => {
        var originalQuantity = item.getAttribute("data-quantity");
        if (originalQuantity) {
            var parts = originalQuantity.split(" ");
            var value = parseFloat(parts[0]);
            var unit = parts.slice(1).join(" ");
            var adjustedValue = (value / 4 * currentServings).toFixed(2);
            if (adjustedValue.endsWith(".00")) adjustedValue = adjustedValue.slice(0, -3);
            var ingredientName = item.querySelector(".ingredient-naam");
            ingredientName.textContent = ingredientName.textContent.replace(/\([^)]+\)/, `(${adjustedValue} ${unit})`);
        }
    });
};

decreaseButton.addEventListener("click", () => updateServings(currentServings - 1));
increaseButton.addEventListener("click", () => updateServings(currentServings + 1));

var alternatieventekst = document.querySelectorAll("#alternatieven-tekst").forEach((item) => {
    var checkbox = item.querySelector(".alternative-checkbox");
    if (checkbox) {
        var ingredientId = item.closest(".ingredient-item").dataset.ingredientId;
        checkbox.setAttribute('name', `ingredient-${ingredientId}`);

        var ecoscoreString = checkbox.dataset.altEcoscore;
        var ecoscores = 0;
        var match = ecoscoreString.match(/\d+/);
        if (match) {
            ecoscores = parseInt(match[0]);
        }

        var ingredientItem = checkbox.closest(".ingredient-item");
        var ingredientNaam = ingredientItem.querySelector(".ingredient-naam");
        var originalEcoscoreText = ingredientNaam.innerText.split("Ecoscore: ")[1];
        var originalEcoscore = 0;

        if (originalEcoscoreText && originalEcoscoreText !== "None") {
            originalEcoscore = parseInt(originalEcoscoreText);
        }

        var lastCheckedEcoscore = 0;

        checkbox.addEventListener("change", (event) => {
            if (event.target.checked) {
                if (lastCheckedEcoscore) {
                    origineleEcoscore -= lastCheckedEcoscore;
                }

                origineleEcoscore -= originalEcoscore;
                origineleEcoscore += ecoscores;

                if (originalEcoscoreText === "None") {
                    maxPosEcoscore.innerText = parseInt(maxPosEcoscore.innerText) + 100;
                }

                lastCheckedEcoscore = ecoscores;

                document.querySelectorAll(`input[name="ingredient-${ingredientId}"]`).forEach((otherCheckbox) => {
                    if (otherCheckbox !== event.target) {
                        otherCheckbox.checked = false;
                    }
                });
            } else {
                origineleEcoscore += originalEcoscore;
                origineleEcoscore -= ecoscores;

                if (originalEcoscoreText === "None") {
                    maxPosEcoscore.innerText = parseInt(maxPosEcoscore.innerText) - 100;
                }

                lastCheckedEcoscore = 0;
            }

            updateEcoscore();
        });
    }
});

var ingredientNaam = document.querySelectorAll(".ingredient-naam");
ingredientNaam.forEach(element => {
    if (element.textContent.includes("Ecoscore: None")) {
        var adjustedScore = parseInt(maxPosEcoscore.innerText) - 100;
        maxPosEcoscore.innerText = adjustedScore;
    }
});

function updateEcoscore() {
    if (isNaN(origineleEcoscore)) {
        origineleEcoscore = 0;
    }

    huidigeEcoscore.innerText = `Ecoscore: ${origineleEcoscore}`;
    
    var ecoscorePositie = (origineleEcoscore / maxScore) * 100;
    var ecoscoreLine = document.querySelector(".ecoscore-line");
    if (ecoscoreLine) {
        ecoscoreLine.style.left = ecoscorePositie + "%";
    }
}
