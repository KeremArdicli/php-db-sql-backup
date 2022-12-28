// DB connection details
$host = "localhost:3306";
$dbname = "dbname";
$username = "user";
$password = "pass";
    
// new PDO
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname; charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Get the names of all tables in the database
$tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_NUM);

// start composing the content of sql file
$return = '';
foreach ($tables as $table) {

    $result = $conn->query("SELECT * FROM " . $table[0]);
    $num_fields = $result->columnCount();

    $return .= 'DROP TABLE ' . $table[0] . ';';
    $row2 = $conn->query("SHOW CREATE TABLE " . $table[0])->fetch(PDO::FETCH_BOTH);
    $return .= "\n\n" . $row2[1] . ";\n\n";

    for ($i = 0; $i < $num_fields; $i++) {
        while ($row = $result->fetch(PDO::FETCH_BOTH)) {
            $return .= "INSERT INTO " . $table[0] . " VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                $row[$j] = addslashes($row[$j]);
                if (isset($row[$j])) {
                    $return .= '"' . $row[$j] . '"';
                } else {
                    $return .= '""';
                }
                if ($j < $num_fields - 1) {
                    $return .= ',';
                }
            }
            $return .= ");\n";
        }
    }
    $return .= "\n\n\n";
}
//save file
$date = date("Ymd");
$handle = fopen("./backup_$date.sql", "w+");
fwrite($handle, $return);
fclose($handle);
