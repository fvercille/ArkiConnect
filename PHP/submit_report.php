<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $org_rep_id = $_SESSION['user_id'];
    $org_name = $_POST['org_name'];
    $report_title = $_POST['report_title'];
    $date_submitted = date('Y-m-d');
    $status = 'Pending Review';
    $attendees = $_POST['attendees'];
    $budget = $_POST['budget'];
    $summary = $_POST['summary'];

    $stmt = $conn->prepare("INSERT INTO org_reports (org_name, report_title, date_submitted, status, attendees, budget, summary, org_rep_id)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssisdsi", $org_name, $report_title, $date_submitted, $status, $attendees, $budget, $summary, $org_rep_id);
    $stmt->execute();

    header("Location: OrgRep_db.php?msg=success");
    exit;
}
?>
