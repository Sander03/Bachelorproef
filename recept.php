<?php
include 'db.php';
$conn = create_Connection();

$defaultServings = 4;

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM recepten WHERE id = ?";
    $result = getQuery($conn, $query, [$id]);

    if (count($result) > 0) {
        $recept = $result[0];
        $ingredientenDB = $recept['ingredienten'];

        $ingredientSplit = explode("-------", $ingredientenDB);
        $aantalIngredienten = count($ingredientSplit);
        $maxScore = ($aantalIngredienten * 100) - 100;
        $ecoscorePositie = ($recept['ecoscore'] / $maxScore) * 100;
    } else {
        $error = "Geen recepten gevonden :(";
    }
} else {
    $error = "Geen recepten in database";
}

closeConnection($conn);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($recept['naam']); ?></title>
    <link rel="stylesheet" href="assets/css/recept.css">
    <script>
        var defaultServings = <?php echo $defaultServings; ?>;
    </script>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="recepten.php">Recepten</a>
        <a href="about.php">About</a>
    </div>

    <div class="recept-container">
        <?php if (isset($recept)) { ?>
            <div class="ingredienten-kolom">
                <h2>Ingrediënten</h2>

                <div class="porties-class">
                    <span id="porties-aantal"><?php echo $defaultServings; ?> personen</span>
                    <button id="verminder-porties" class="porties-button">-</button>
                    <button id="vermeerder-porties" class="porties-button">+</button>
                </div>

                <ul class="ingredienten-lijst">
                    <?php foreach ($ingredientSplit as $index => $parts) { 
                        $lines = explode("\n", trim($parts));

                        if (empty($lines[0])) {
                            continue;
                        }

                        $naamEnEcoscore = array_shift($lines);
                        $origineleIngredientEcoscoreCheck = explode(" ", $naamEnEcoscore);
                        $OriginalIngredientScore = end($origineleIngredientEcoscoreCheck);

                        $naamEnQuantity = explode("-", $naamEnEcoscore)[0];
                        $splitNaamEnQuantity = explode(" ", $naamEnQuantity);
                        $OriginalIngredNaam = ($splitNaamEnQuantity[0]);
                        $quantityCheck = explode(" ", $naamEnQuantity)[1];
                        $quantity = str_replace(['(', ')'], '', $quantityCheck);

                        $alternatives = [];
                        foreach ($lines as $line) {
                            if (strpos($line, "- ") === 0){
                                $alternative = trim($line);
                                if ($alternative === "- Geen alternatieven beschikbaar") {
                                    $alternative = "- Koop lokaal/bio - Ecoscore: 75";
                                }
                                $alternatives[] = $alternative;
                            }
                        }
                    ?>
                        <li class="ingredient-item" data-ingredient-id="<?php echo $index; ?>" data-quantity="<?php echo($quantity); ?>" data-original-ecoscore="<?php echo $OriginalIngredientScore; ?>">
                            <span class="ingredient-naam"><?php echo htmlspecialchars($naamEnEcoscore); ?></span>
                            <div class="alternative-section">
                                <span id="alternatieven-woord">Alternatieven:</span>
                                <ul class="alternative-list">
                                    <?php foreach ($alternatives as $alt) { 
                                        $splitAlts = explode(" - ", $alt);
                                        $AltsNameCheck = $splitAlts[0];
                                        $AltName = $AltsNameCheck;
                                        $altgetEcoscore = explode(" ", end($splitAlts));
                                        $altEcoscore = end($altgetEcoscore);
                                    ?>
                                        <p id="alternatieven-tekst">
                                            <?php echo htmlspecialchars($alt); ?> 
                                            <input type="checkbox" class="alternative-checkbox" data-alt-name="<?php echo htmlspecialchars($AltName); ?>" data-alt-ecoscore="<?php echo $altEcoscore; ?>">
                                        </p>
                                    <?php } ?>
                                </ul>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>

            <div class="bereiding-kolom">
                <div class="title-container">
                    <img src="<?php echo htmlspecialchars($recept['image_url']); ?>" alt="Recept Afbeelding" class="recept-image">
                    <h1 class="recipe-title"><?php echo htmlspecialchars($recept['naam']); ?></h1>
                </div>
                <br><br><br>
                <div class="ecoscore-tooltip">
                    <div id="ecoscore">
                        <span id="huidige-ecoscore">Ecoscore: <?php echo htmlspecialchars($recept['ecoscore']); ?></span>/<span id="max-ecoscore"><?php echo $maxScore; ?></span>
                        <span id="tooltiptekst">De ecoscore geeft weer hoe duurzaam het gerecht is. Hoe hoger de score, hoe beter voor het milieu.</span>
                    </div>
                </div>
                <div class="ecoscore-container">
                    <div class="ecoscore-bar">
                        <div class="ecobar" id="ecobad"><span>E</span></div>
                        <div class="ecobar" id="ecobad2"><span>D</span></div>
                        <div class="ecobar" id="ecomid"><span>C</span></div>
                        <div class="ecobar" id="ecobetter"><span>B</span></div>
                        <div class="ecobar" id="ecogood"><span>A</span></div>
                        <div class="ecoscore-line" style="left: <?php echo $ecoscorePositie; ?>%;"></div>
                    </div>
                </div>
                <h2 id="bereiding-titel">Bereiding</h2>
                <p><?php echo nl2br(htmlspecialchars($recept['bereiding'])); ?></p>
            </div>
        <?php } else { ?>
            <p><?php echo htmlspecialchars($error); ?></p>
        <?php } ?>
    </div>
    <script src="assets/js/recept.js"></script>
</body>
</html>
