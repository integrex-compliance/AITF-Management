    <?php

 include_once './dbConnect.php';



$sql = "

WITH RECURSIVE

numbers AS (

    SELECT 1 AS n

    UNION ALL

    SELECT n + 1

    FROM numbers

    WHERE n < 20

),



all_audits AS (

    SELECT

        'CA-STAGE-I' AS service_type,

        ca.customer_id,

        ci.customer_name AS client_name,

        ca.audit_date,

        ca.stage1_auditor AS auditors

    FROM service_type_ca ca

    LEFT JOIN customer_info ci

        ON ci.id = CAST(ca.customer_id AS UNSIGNED)



    UNION ALL



    SELECT

        'CA-STAGE-II',

        ca.customer_id,

        ci.customer_name,

        ca.stage2_audit,

        ca.stage2_auditor

    FROM service_type_ca ca

    LEFT JOIN customer_info ci

        ON ci.id = CAST(ca.customer_id AS UNSIGNED)



    UNION ALL



    SELECT

        'SA-I',

        sai.customer_id,

        ci.customer_name,

        sai.audit_date,

        sai.auditors

    FROM service_type_sai sai

    LEFT JOIN customer_info ci

        ON ci.id = CAST(sai.customer_id AS UNSIGNED)



    UNION ALL



    SELECT

        'SA-II',

        saii.customer_id,

        ci.customer_name,

        saii.audit_date,

        saii.auditors

    FROM service_type_saii saii

    LEFT JOIN customer_info ci

        ON ci.id = CAST(saii.customer_id AS UNSIGNED)



    UNION ALL



    SELECT

        'RC',

        rc.rc_customer,

        ci.customer_name,

        rc.audit_date,

        rc.auditors

    FROM service_type_rc rc

    LEFT JOIN customer_info ci

        ON ci.id = CAST(rc.rc_customer AS UNSIGNED)

),



split_dates AS (

    SELECT

        aa.service_type,

        aa.customer_id,

        aa.client_name,

        aa.auditors,

        TRIM(

            SUBSTRING_INDEX(

                SUBSTRING_INDEX(aa.audit_date, ',', numbers.n),

                ',',

                -1

            )

        ) AS audit_date_text

    FROM all_audits aa

    JOIN numbers

        ON numbers.n <= 1

        + LENGTH(COALESCE(aa.audit_date, ''))

        - LENGTH(REPLACE(COALESCE(aa.audit_date, ''), ',', ''))

    WHERE aa.audit_date IS NOT NULL

      AND TRIM(aa.audit_date) <> ''

),



split_auditors AS (

    SELECT

        sd.service_type,

        sd.customer_id,

        sd.client_name,

        STR_TO_DATE(sd.audit_date_text, '%d.%m.%Y') AS audit_dt,

        TRIM(

            SUBSTRING_INDEX(

                SUBSTRING_INDEX(sd.auditors, ',', numbers.n),

                ',',

                -1

            )

        ) AS auditor

    FROM split_dates sd

    JOIN numbers

        ON numbers.n <= 1

        + LENGTH(COALESCE(sd.auditors, ''))

        - LENGTH(REPLACE(COALESCE(sd.auditors, ''), ',', ''))

),



clean_data AS (

    SELECT DISTINCT

        service_type,

        CAST(customer_id AS UNSIGNED) AS customer_id,

        client_name,

        audit_dt,

        auditor

    FROM split_auditors

    WHERE audit_dt IS NOT NULL

      AND auditor IS NOT NULL

      AND TRIM(auditor) <> ''

      AND UPPER(TRIM(auditor)) NOT IN (

          'NA',

          'N.A.',

          'N/A',

          'SAME LA',

          'NOT REQUIRED'

      )

),



conflicts AS (

    SELECT

        auditor,

        audit_dt

    FROM clean_data

    GROUP BY auditor, audit_dt

    HAVING COUNT(DISTINCT customer_id) > 1

)



SELECT

    cd.auditor,

    cd.audit_dt,

    cd.customer_id,

    cd.client_name,

    cd.service_type

FROM clean_data cd

INNER JOIN conflicts c

    ON c.auditor = cd.auditor

    AND c.audit_dt = cd.audit_dt

ORDER BY

    cd.audit_dt DESC,

    cd.auditor ASC,

    cd.client_name ASC

";



$stmt = $db->prepare($sql);

$stmt->execute();

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);



$report = [];



foreach ($rows as $row) {

    $key = $row['auditor'] . '|' . $row['audit_dt'];



    if (!isset($report[$key])) {

        $report[$key] = [

            'auditor' => $row['auditor'],

            'audit_date' => $row['audit_dt'],

            'clients' => []

        ];

    }



    $report[$key]['clients'][] = $row;

}

?>



<!DOCTYPE html>

<html lang="en">



<head>

    <meta charset="UTF-8">

    <title>Auditor Audit Conflict Report</title>



    <style>

        body {

            font-family: Arial, sans-serif;

            background: #f5f6fa;

            margin: 0;

            padding: 30px;

            color: #222;

        }



        .container {

            max-width: 1400px;

            margin: auto;

            background: #fff;

            padding: 25px;

            border-radius: 8px;

        }



        h1 {

            margin-top: 0;

            font-size: 26px;

        }



        .summary {

            margin-bottom: 20px;

            font-size: 16px;

        }



        table {

            width: 100%;

            border-collapse: collapse;

        }



        th {

            background: #0d4d7d;

            color: #fff;

            padding: 12px;

            text-align: left;

            border: 1px solid #ddd;

        }



        td {

            padding: 11px;

            border: 1px solid #ddd;

            vertical-align: top;

        }



        .conflict-start td {

            border-top: 3px solid #d32f2f;

        }



        .count {

            font-weight: bold;

            text-align: center;

        }



        .danger {

            color: #c62828;

            font-weight: bold;

        }



        .print-btn {

            background: #0d4d7d;

            color: white;

            border: 0;

            padding: 10px 20px;

            cursor: pointer;

            margin-bottom: 20px;

            font-size: 15px;

        }



        @media print {

            body {

                background: white;

                padding: 0;

            }



            .container {

                max-width: none;

                padding: 0;

            }



            .print-btn {

                display: none;

            }

        }

    </style>

</head>



<body>



<div class="container">



    <h1>Auditor Same Date Audit Conflict Report</h1>



    <div class="summary">

        Total Conflict Groups:

        <strong><?= count($report) ?></strong>

    </div>



    <button class="print-btn" onclick="window.print()">

        Print Report

    </button>



    <table>



        <thead>

        <tr>

            <th>Sr.</th>

            <th>Auditor</th>

            <th>Audit Date</th>

            <th>Client Count</th>

            <th>Client Name</th>

            <th>Client ID</th>

            <th>Service Type</th>

        </tr>

        </thead>



        <tbody>



        <?php

        $sr = 1;



        foreach ($report as $group):



            $clientCount = count(

                array_unique(

                    array_column($group['clients'], 'customer_id')

                )

            );



            $first = true;



            foreach ($group['clients'] as $client):

        ?>



        <tr class="<?= $first ? 'conflict-start' : '' ?>">



            <?php if ($first): ?>



                <td rowspan="<?= count($group['clients']) ?>">

                    <?= $sr ?>

                </td>



                <td rowspan="<?= count($group['clients']) ?>">

                    <strong>

                        <?= htmlspecialchars($group['auditor']) ?>

                    </strong>

                </td>



                <td rowspan="<?= count($group['clients']) ?>">

                    <?= date('d.m.Y', strtotime($group['audit_date'])) ?>

                </td>



                <td

                    rowspan="<?= count($group['clients']) ?>"

                    class="count danger"

                >

                    <?= $clientCount ?>

                </td>



            <?php endif; ?>



            <td>

                <?= htmlspecialchars($client['client_name'] ?? 'Unknown Client') ?>

            </td>



            <td>

                <?= (int)$client['customer_id'] ?>

            </td>



            <td>

                <?= htmlspecialchars($client['service_type']) ?>

            </td>



        </tr>



        <?php

                $first = false;

            endforeach;



            $sr++;



        endforeach;

        ?>



        </tbody>



    </table>



</div>



</body>

</html>
