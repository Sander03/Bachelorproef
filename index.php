<?php
include 'db.php';
$conn = create_Connection();

$query = "SELECT id, naam FROM recepten LIMIT 3";
$result = mysqli_query($conn, $query);
$recepten = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $recepten[] = $row;
    }
}
closeConnection($conn);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="recepten.php">Recepten</a>
        <a href="about.php">About</a>
    </div>

    <div class="intro">
        <div class="intro-text">
            <p>
                Het doel van deze website is om de impact van voedsel op het milieu te onderzoeken en te begrijpen. 
                Van het begrijpen van de ecologische voetafdruk van je favoriete recepten tot het ontdekken van milieuvriendelijke alternatieven, 
                daarom streven we om je te helpen bij het maken van duurzamere keuzes.
            </p>
        </div>
        <div class="intro-image">
            <img src="assets/images/ecoscore.jpg" alt="Ecoscore Image">
        </div>
    </div>

    <div class="container">
        <div class="header-recepten">
            <h2>Recepten</h2>
            <a href="recepten.php" class="see-all-recipes">Zie alle recepten</a>
        </div>
        <div class="recept-container">
            <?php if (!empty($recepten)) : ?>
                <?php foreach ($recepten as $recept) : ?>
                    <div class="recept">
                        <span class="recept-title"><?php echo ($recept['naam']); ?></span>
                        <br>
                        <a href="recepten.php" class="recept-button">Bekijk recepten</a>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p>Er zijn momenteel geen recepten beschikbaar.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
