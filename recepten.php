<?php
include 'db.php';
$conn = create_Connection();

$query = "SELECT id, naam, ecoscore FROM recepten";
$recepten = getQuery($conn, $query);

closeConnection($conn);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recepten Overzicht</title>
    <link rel="stylesheet" href="assets/css/recepten.css">
</head>
<body>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="recepten.php">Recepten</a>
        <a href="about.php">About</a>
    </div>

    <div class="container">
        <h1>Overzicht van Recepten</h1>
        <?php if (!empty($recepten)) : ?>
            <table class="recepten-tabel">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <th>Ecoscore</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recepten as $recept) : ?>
                        <tr>
                            <td><?php echo ($recept['naam']); ?></td>
                            <td><?php echo ($recept['ecoscore']); ?></td>
                            <td>
                                <a href="recept.php?id=<?php echo $recept['id']; ?>" class="toggle-details">
                                    Bekijk recept
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>Geen recepten beschikbaar</p>
        <?php endif; ?>
    </div>
    <script src="assets/js/recepten.js"></script>
</body>
</html>
