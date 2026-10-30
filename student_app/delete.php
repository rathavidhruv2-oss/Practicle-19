<?php
// ============================================================
// delete.php — Delete Student Record
// Purpose : Receives a student ID from the URL, deletes the
//           matching row using a prepared statement, then
//           redirects back to index.php with a status message.
// ============================================================

require_once 'db.php'; // Include the DB connection

// ---- Get and validate ID from URL ----
// intval() ensures it's a whole number; garbage input becomes 0
$id = intval($_GET['id'] ?? 0);

// If ID is invalid, redirect immediately (do nothing)
if ($id <= 0) {
    header("Location: index.php?msg=invalid");
    exit();
}

// ============================================================
// Prepared Statement for DELETE
// Using ? placeholder so the ID is never embedded raw in SQL
// ============================================================
$stmt = $conn->prepare("DELETE FROM students WHERE id = ?");
$stmt->bind_param("i", $id);   // "i" = integer type binding

if ($stmt->execute()) {
    // affected_rows tells us if a row was actually deleted
    if ($stmt->affected_rows > 0) {
        // Deleted successfully — redirect with success flag
        header("Location: index.php?deleted=1");
    } else {
        // ID did not exist in the table
        header("Location: index.php?deleted=0");
    }
} else {
    // Query itself failed (DB error)
    header("Location: index.php?deleted=error");
}

$stmt->close();   // Close the statement
$conn->close();   // Close the connection
exit();           // Stop script after redirect
?>
