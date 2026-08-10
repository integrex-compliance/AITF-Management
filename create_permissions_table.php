<?php
include_once 'dbConnect.php';
global $conn;
$sql = "CREATE TABLE IF NOT EXISTS `user_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `feature` varchar(50) NOT NULL,
  `is_allowed` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_feature_unique` (`user_id`,`feature`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if ($conn->query($sql) === TRUE) {
    echo "Table user_permissions created successfully\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
?>
