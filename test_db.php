<?php include('dbConnect.php'); try { \ = \->query('SELECT * FROM service_data LIMIT 1'); print_r(\->fetchAll()); } catch(PDOException \) { echo 'Error: ' . \->getMessage(); }
