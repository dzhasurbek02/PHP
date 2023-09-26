<?php
$db = mysqli_connect("localhost", "root", "", "bookdb");
?>
<html>

<head>
    <meta charset="utf-8">
    <title>Список пользователей</title>

</head>

<body>

    <form>

        <!-- <select name="category" id="1">
        <option value="">Select category</option>
        <option value="1">Cat 1</option>
        <option value="2">Cat 2</option>
        <option value="3">Cat 3</option>
    </select> -->

        <select name="cat" onchange="pokazTovarov(this.value)" style="height:50px;">
            <option value="">Список пользователей</option>
            <?php
            $result = mysqli_query($db, "SELECT * FROM users");
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<option value='" . $row['user_id'] . "'>";
                echo $row['username'];
                echo "</option>";
            }
            ?>
        </select>
    </form>

    <div id="InfoTovarov">

    </div>


    <script type="text/javascript">
        function pokazTovarov(categoriyaId) {
            var xmlhttp;
            if (window.XMLHttpRequest) {
                xmlhttp = new XMLHttpRequest();
            } else {
                xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
            }

            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                    document.getElementById("InfoTovarov").innerHTML = xmlhttp.responseText;
                }
            }

            xmlhttp.open("GET", "getInfo.php?q=" + categoriyaId, true);
            xmlhttp.send();

        }
    </script>

</body>

</html>