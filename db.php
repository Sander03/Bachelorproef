<?php
    function create_Connection()
    {
        $hostname = "ID362601_receptenDB.db.webhosting.be";
        $dbUser = "ID362601_receptenDB";
        $dbPassword = "sj0Ae0K148404Z960uXU";
        $dbName = "ID362601_receptenDB";

        $conn = mysqli_connect($hostname, $dbUser, $dbPassword, $dbName);

        if($conn == false)
        {
            echo "Broken Connection";
        die();
        } else

        return $conn;
    }
    function getQuery($conn, $query, $params = [])
{
    $stmt = $conn->prepare($query);

    if ($params) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_all(MYSQLI_ASSOC);
}

    function closeConnection($conn)
    {
        $conn->close();
    }
?>