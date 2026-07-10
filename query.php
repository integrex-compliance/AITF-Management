<?php
include_once './dbConnect.php';

function cleanName($name)
{
    $name = trim((string)$name);
    $name = preg_replace('/\s+/', ' ', $name);

    return $name;
}

function normalizeName($name)
{
    $name = cleanName($name);
    return strtoupper($name);
}

function isValidPerson($name)
{
    $name = normalizeName($name);

    return !in_array($name, [
        '',
        'NA',
        'N.A.',
        'N/A',
        'N.A',
        '-',
        '--',
        '---',
        'NOT REQUIRED',
        'SAME LA'
    ], true);
}

function splitPersons($value)
{
    $parts = preg_split(
        '/[,;\r\n]+/',
        (string)$value
    );

    $persons = [];

    foreach ($parts as $part) {

        $name = cleanName($part);

        if (!isValidPerson($name)) {
            continue;
        }

        $key = normalizeName($name);

        $persons[$key] = $name;
    }

    return $persons;
}

$sql = "
    SELECT
        crr_id,
        customer_id,
        audit_organization,
        audit_date,
        audit_type,
        lead_auditor,
        auditors,
        reviewer_1,
        reviewer_2,
        granted_by,
        cdc_status
    FROM cdc_report_review
    ORDER BY crr_id DESC
";

$stmt = $db->prepare($sql);
$stmt->execute();

$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$conflicts = [];

foreach ($rows as $row) {

    $auditTeam = [];

    /*
    |--------------------------------------------------------------------------
    | Lead Auditor
    |--------------------------------------------------------------------------
    */

    if (isValidPerson($row['lead_auditor'])) {

        $key = normalizeName(
            $row['lead_auditor']
        );

        $auditTeam[$key] = [
            'name' => cleanName(
                $row['lead_auditor']
            ),
            'roles' => [
                'Lead Auditor'
            ]
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Other Auditors
    |--------------------------------------------------------------------------
    */

    $auditors = splitPersons(
        $row['auditors']
    );

    foreach ($auditors as $key => $name) {

        if (!isset($auditTeam[$key])) {

            $auditTeam[$key] = [
                'name' => $name,
                'roles' => []
            ];
        }

        $auditTeam[$key]['roles'][] =
            'Auditor';
    }

    /*
    |--------------------------------------------------------------------------
    | Review / Granting Roles
    |--------------------------------------------------------------------------
    */

    $reviewRoles = [
        'Reviewer 1' => $row['reviewer_1'],
        'Reviewer 2' => $row['reviewer_2'],
        'Granted By' => $row['granted_by']
    ];

    foreach ($reviewRoles as $role => $person) {

        if (!isValidPerson($person)) {
            continue;
        }

        $personKey = normalizeName($person);

        /*
        |--------------------------------------------------------------------------
        | Conflict Found
        |--------------------------------------------------------------------------
        */

        if (isset($auditTeam[$personKey])) {

            $conflicts[] = [

                'crr_id' =>
                    $row['crr_id'],

                'customer_id' =>
                    $row['customer_id'],

                'client_name' =>
                    $row['audit_organization'],

                'audit_date' =>
                    $row['audit_date'],

                'audit_type' =>
                    $row['audit_type'],

                'person' =>
                    $auditTeam[$personKey]['name'],

                'audit_role' =>
                    implode(
                        ', ',
                        array_unique(
                            $auditTeam[$personKey]['roles']
                        )
                    ),

                'conflict_role' =>
                    $role,

                'cdc_status' =>
                    $row['cdc_status']
            ];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
Reviewer Independence Conflict Report
</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 30px;
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f6f8;
    color: #1f2937;
}

.report {
    max-width: 1500px;
    margin: auto;
    background: white;
    padding: 28px;
    border: 1px solid #ddd;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 25px;
}

h1 {
    margin: 0 0 8px;
    font-size: 26px;
}

.subtitle {
    margin: 0;
    color: #667085;
    font-size: 14px;
}

.summary {
    margin-bottom: 20px;
}

.summary-box {
    display: inline-block;
    min-width: 220px;
    padding: 15px 20px;
    border: 1px solid #ddd;
    background: #fafafa;
}

.summary-label {
    display: block;
    color: #667085;
    font-size: 12px;
    margin-bottom: 5px;
}

.summary-value {
    font-size: 25px;
    font-weight: bold;
}

.print-btn {
    border: 0;
    padding: 10px 18px;
    background: #174f7a;
    color: white;
    cursor: pointer;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

th {
    background: #174f7a;
    color: white;
    padding: 11px 9px;
    border: 1px solid #123d5e;
    text-align: left;
}

td {
    padding: 10px 9px;
    border: 1px solid #ddd;
    vertical-align: top;
}

.conflict {
    font-weight: bold;
}

.no-data {
    text-align: center;
    padding: 30px;
}

@media print {

    body {
        background: white;
        padding: 0;
    }

    .report {
        max-width: none;
        border: 0;
        padding: 0;
    }

    .print-btn {
        display: none;
    }

    table {
        font-size: 10px;
    }

    th,
    td {
        padding: 6px;
    }
}

</style>

</head>

<body>

<div class="report">

<div class="header">

<div>

<h1>
Reviewer Independence Conflict Report
</h1>

<p class="subtitle">
Audit team members appearing in Reviewer 1,
Reviewer 2 or Granted By roles.
</p>

</div>

<button
    class="print-btn"
    onclick="window.print()"
>
Print Report
</button>

</div>


<div class="summary">

<div class="summary-box">

<span class="summary-label">
Total Conflicts
</span>

<span class="summary-value">
<?= count($conflicts) ?>
</span>

</div>

</div>


<table>

<thead>

<tr>

<th>Sr.</th>

<th>Client Name</th>

<th>Client ID</th>

<th>Audit Date</th>

<th>Audit Type</th>

<th>Person</th>

<th>Audit Role</th>

<th>Conflicting Role</th>

<th>CDC Status</th>

<th>CRR ID</th>

</tr>

</thead>


<tbody>

<?php if (empty($conflicts)): ?>

<tr>

<td
    colspan="10"
    class="no-data"
>

No reviewer independence conflicts found.

</td>

</tr>

<?php else: ?>


<?php foreach (
    $conflicts as $index => $conflict
): ?>

<tr>

<td>
<?= $index + 1 ?>
</td>

<td>
<?= htmlspecialchars(
    $conflict['client_name']
) ?>
</td>

<td>
<?= (int)$conflict['customer_id'] ?>
</td>

<td>
<?= htmlspecialchars(
    $conflict['audit_date']
) ?>
</td>

<td>
<?= htmlspecialchars(
    $conflict['audit_type']
) ?>
</td>

<td class="conflict">
<?= htmlspecialchars(
    $conflict['person']
) ?>
</td>

<td>
<?= htmlspecialchars(
    $conflict['audit_role']
) ?>
</td>

<td class="conflict">
<?= htmlspecialchars(
    $conflict['conflict_role']
) ?>
</td>

<td>
<?= htmlspecialchars(
    $conflict['cdc_status']
) ?>
</td>

<td>
<?= (int)$conflict['crr_id'] ?>
</td>

</tr>

<?php endforeach; ?>

<?php endif; ?>

</tbody>

</table>

</div>

</body>

</html>