<?php
$servername = "localhost";
$username = "root";
$password = "Spica2024!";
$dbname = "rfid";

// Ustvarimo povezavo
$conn = new mysqli($servername, $username, $password, $dbname);

// Preverimo povezavo
if ($conn->connect_error) {
    die("Povezava ni uspela: " . $conn->connect_error);
}

// SQL poizvedba za vstavljanje zdru�enih podatkov
$sql = "
INSERT INTO book_user_attendance (
    book_id, 
    bookname, 
    author_first_name, 
    author_last_name, 
    book_timestamp, 
    book_antenna, 
    book_antenna_location, 
    user_timestamp, 
    user_antenna, 
    user_antenna_location, 
    username
)
SELECT
    t.idHex AS book_id,
    IFNULL(b.bookname, 'Neznana knjiga') AS bookname,
    IFNULL(b.ime, '') AS author_first_name,
    IFNULL(b.priimek, '') AS author_last_name,
    t.timestamp AS book_timestamp, -- Ni potrebno kraj�ati timestamp, ker je �e pravilne oblike
    t.antenna AS book_antenna,
    IFNULL(bl.location, '') AS book_antenna_location,
    ua.timestamp AS user_timestamp,
    ua.antenna AS user_antenna,
    IFNULL(ul.location, '') AS user_antenna_location,
    IFNULL(u.username, 'Neznan uporabnik') AS username
FROM 
    test t
-- Pridru�itev na `book_names`, da dobimo informacije o knjigah
LEFT JOIN 
    book_names b ON t.idHex = b.idHex
-- Pridru�itev na `location_data` za lokacijo knjige
LEFT JOIN 
    location_data bl ON t.antenna = bl.antenna
-- Pridru�itev na `user_attendance`, ki temelji na �asovnem razponu +-25 sekund
LEFT JOIN 
    user_attendance ua ON ABS(UNIX_TIMESTAMP(ua.timestamp) - UNIX_TIMESTAMP(t.timestamp)) <= 25
-- Pridru�itev na `location_data` za lokacijo uporabnika
LEFT JOIN 
    location_data ul ON ua.antenna = ul.antenna
-- Pridru�itev na `users`, da pridobimo uporabni�ko ime
LEFT JOIN 
    users u ON ua.user_id = u.id
";

if ($conn->query($sql) === TRUE) {
    echo "Podatki uspe�no vstavljeni.";
} else {
    echo "Napaka pri vnosu podatkov: " . $conn->error;
}

$conn->close();
?>
