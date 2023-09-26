<?php
$db = mysqli_connect("localhost", "root", "", "bookdb");

$userId = (int) $_GET['q'];

$result = mysqli_query($db, "SELECT * FROM users WHERE user_id = ".$userId."");

echo "<table border='1'>
        <tr>
            <th>№</th>
            <th>username</th>
            <th>email</th>
            <th>password_hash</th>
            <th>isRoot</th>
        </tr>
";

echo mysqli_num_rows($result);

while ($row = mysqli_fetch_assoc($result))
{
    echo "
        <tr>
            <td>".$row['user_id']."</td>
            <td>".$row['username']."</td>
            <td>".$row['email']."</td>
            <td>".$row['password']."</td>
            <td>".$row['isRoot']."</td>
        </tr>
    ";

}

echo "</table>";

mysqli_close($db);
?>