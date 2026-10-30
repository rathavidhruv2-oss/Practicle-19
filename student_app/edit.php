<?php
// ============================================================
// edit.php — Edit / Update Student Record
// Purpose : 1. Fetch the existing record using the ID from URL.
//           2. Pre-fill the form with existing values.
//           3. Handle form submission and UPDATE the record.
// ============================================================

require_once 'db.php'; // Include DB connection

$success = '';
$error   = '';

// ---- Get and validate the student ID from the URL (?id=3) ----
// intval() converts to integer — rejects non-numeric input
$id = intval($_GET['id'] ?? 0);

// If ID is 0 or missing, redirect back to index
if ($id <= 0) {
    header("Location: index.php");
    exit();
}

// ============================================================
// HANDLE UPDATE FORM SUBMISSION
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {

    // Retrieve and trim inputs
    $name   = trim($_POST['name']   ?? '');
    $email  = trim($_POST['email']  ?? '');
    $course = trim($_POST['course'] ?? '');

    // ---------- Validation ----------
    if ($name === '' || $email === '' || $course === '') {
        $error = "⚠️ All fields are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "⚠️ Invalid email address.";

    } else {
        // ---- Prepared Statement for UPDATE ----
        // ? placeholders keep user input separate from SQL syntax
        $stmt = $conn->prepare(
            "UPDATE students SET name = ?, email = ?, course = ? WHERE id = ?"
        );

        // "sssi" = three strings + one integer
        $stmt->bind_param("sssi", $name, $email, $course, $id);

        if ($stmt->execute()) {
            $success = "✅ Student record updated successfully!";
        } else {
            $error = "❌ Update failed. Email may already be used by another student.";
        }

        $stmt->close();
    }
}

// ============================================================
// FETCH current record to pre-fill the form
// Uses prepared statement because $id comes from URL (user input)
// ============================================================
$stmt = $conn->prepare("SELECT id, name, email, course FROM students WHERE id = ?");
$stmt->bind_param("i", $id);   // "i" = integer
$stmt->execute();

// Get the result set from the prepared statement
$result = $stmt->get_result();
$student = $result->fetch_assoc(); // Fetch as associative array
$stmt->close();

// If no student found with this ID, go back to the list
if (!$student) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student — Student Management System</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4f8;
            color: #333;
            padding: 20px;
        }
        .container { max-width: 600px; margin: 0 auto; }
        h1 {
            background: #2c3e50; color: #fff;
            padding: 18px 24px; border-radius: 8px 8px 0 0; font-size: 22px;
        }
        .card {
            background: #fff; border-radius: 8px;
            padding: 28px; margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        h2 { font-size: 18px; margin-bottom: 18px; color: #2c3e50; }
        .msg-success {
            background: #d4edda; color: #155724;
            border: 1px solid #c3e6cb;
            padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;
        }
        .msg-error {
            background: #f8d7da; color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px 16px; border-radius: 6px; margin-bottom: 16px;
        }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 6px; }
        .form-group input {
            width: 100%; padding: 10px 12px;
            border: 1px solid #ccc; border-radius: 5px; font-size: 14px;
        }
        .form-group input:focus { outline: none; border-color: #3498db; }
        .btn-row { display: flex; gap: 12px; margin-top: 10px; }
        .btn {
            padding: 10px 22px; border: none; border-radius: 5px;
            cursor: pointer; font-size: 14px; font-weight: 600;
            text-decoration: none; display: inline-block;
        }
        .btn-success { background: #27ae60; color: #fff; }
        .btn-success:hover { background: #219a52; }
        .btn-secondary { background: #95a5a6; color: #fff; }
        .btn-secondary:hover { background: #7f8c8d; }
    </style>
</head>
<body>
<div class="container">

    <h1>✏️ Edit Student Record</h1>

    <div class="card">
        <h2>Editing: <?= htmlspecialchars($student['name']) ?> (ID: <?= $student['id'] ?>)</h2>

        <!-- Feedback messages -->
        <?php if ($success): ?>
            <div class="msg-success">
                <?= $success ?>
                <a href="index.php" style="color:#155724;">← Back to list</a>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="msg-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Update Form — posts back to same URL with same ?id= -->
        <form method="POST" action="edit.php?id=<?= $id ?>">

            <!-- Name Field — pre-filled with current DB value -->
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name"
                       value="<?= htmlspecialchars($student['name']) ?>" required>
            </div>

            <!-- Email Field — pre-filled -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($student['email']) ?>" required>
            </div>

            <!-- Course Field — pre-filled -->
            <div class="form-group">
                <label for="course">Course</label>
                <input type="text" id="course" name="course"
                       value="<?= htmlspecialchars($student['course']) ?>" required>
            </div>

            <!-- Hidden field to detect this specific form -->
            <input type="hidden" name="update_student" value="1">

            <div class="btn-row">
                <button type="submit" class="btn btn-success">💾 Save Changes</button>
                <!-- Cancel goes back to index without saving -->
                <a href="index.php" class="btn btn-secondary">✖ Cancel</a>
            </div>
        </form>
    </div>

</div>
</body>
</html>
<?php $conn->close(); ?>
