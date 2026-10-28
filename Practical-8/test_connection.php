<?php
include "db.php";

$sql = "SELECT students.full_name, students.email, events.event_name, events.event_date
        FROM registrations
        JOIN students ON registrations.student_id = students.student_id
        JOIN events ON registrations.event_id = events.event_id
        WHERE events.event_date >= ?";

$stmt = mysqli_prepare($conn, $sql);
$today = date("Y-m-d");
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo "<h2>Database connected successfully</h2>";
echo "<h3>Student Event Registrations</h3>";

echo "<table border='1'>";
echo "<tr><th>Name</th><th>Email</th><th>Event</th><th>Date</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row["full_name"]) . "</td>";
    echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
    echo "<td>" . htmlspecialchars($row["event_name"]) . "</td>";
    echo "<td>" . htmlspecialchars($row["event_date"]) . "</td>";
    echo "</tr>";
}

echo "</table>";

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
