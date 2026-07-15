<?php
include_once './dbConnect.php';

// Get the selected NACE Code
$naceCode = $_POST['nace_code'];

$sql = "
    SELECT *
FROM `nace2_classes`
WHERE ifa_division_id = (
    SELECT CAST(iaf_id AS BINARY)
    FROM `nace_codes`
    WHERE nace_code = :id1
)
AND nace_group_id = (
    SELECT CAST(nace_id AS BINARY)
    FROM `nace_codes`
    WHERE nace_code = :id1
);
";
$statement = $db->prepare($sql);
$statement->bindParam(":id1", $naceCode);

$statement->execute();

$nace = $statement->fetchAll(PDO::FETCH_ASSOC);

$sql1 = "SELECT `nace_desc` FROM `nace_codes` WHERE nace_code = :id1;";
$statement1 = $db->prepare($sql1);
$statement1->bindParam(":id1", $naceCode);
$statement1->execute();
$nacedesc = $statement1->fetch();

header('Content-Type: application/json');
echo json_encode($nace);
?>
