<?php
// ============================================================
// index.php — Main Page
// Purpose : 1. Display all student records in an HTML table.
//           2. Show the "Add New Student" form.
//           3. Handle the form submission (INSERT).
// ============================================================

// Include the database connection
require_once 'db.php';

// ---------- Variables to hold feedback messages ----------
$success = '';   // Green success message
$error   = '';   // Red error message

// ---- Show message after a redirect from delete.php ----
if (isset($_GET['deleted'])) {
    if ($_GET['deleted'] === '1') {
        $success = "✅ Student record deleted successfully.";
    } elseif ($_GET['deleted'] === '0') {
        $error = "⚠️ No student found with that ID.";
    } else {
        $error = "❌ Delete operation failed due to a database error.";
    }
}

// ============================================================
// HANDLE FORM SUBMISSION (Create / INSERT)
// Runs only when the form is submitted via POST
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {

    // Retrieve and trim whitespace from form inputs
    $name   = trim($_POST['name']   ?? '');
    $email  = trim($_POST['email']  ?? '');
    $course = trim($_POST['course'] ?? '');

    // ---------- Basic Validation ----------
    if ($name === '' || $email === '' || $course === '') {
        // All fields are required
        $error = "⚠️ All fields are required. Please fill in every field.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // PHP built-in email format validator
        $error = "⚠️ Invalid email address. Please enter a valid email.";

    } else {
        // ---- Prepared Statement for INSERT (prevents SQL injection) ----
        // prepare() creates the query template with ? placeholders
        $stmt = $conn->prepare(
            "INSERT INTO students (name, email, course) VALUES (?, ?, ?)"
        );

        // bind_param() maps PHP variables to the ? placeholders
        // "sss" means three strings
        $stmt->bind_param("sss", $name, $email, $course);

        // execute() runs the query safely
        if ($stmt->execute()) {
            $success = "✅ Student <strong>" . htmlspecialchars($name) . "</strong> added successfully!";
        } else {
            // Duplicate email or other DB error
            $error = "❌ Error: Could not add student. Email may already exist.";
        }

        $stmt->close(); // Always close the statement
    }
}

// ============================================================
// READ — Fetch all students to display in the table
// ============================================================
// Simple SELECT with no user input, so no prepared statement needed here
$result = $conn->query("SELECT id, name, email, course, created_at FROM students ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>
    <style>
        /* ---- Basic Reset & Body ---- */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f4f8;
            color: #333;
            padding: 20px;
        }

        /* ---- Page Container ---- */
        .container { max-width: 1100px; margin: 0 auto; }

        /* ---- Header ---- */
        h1 {
            background: #2c3e50;
            color: #fff;
            padding: 18px 24px;
            border-radius: 8px 8px 0 0;
            font-size: 22px;
        }
        h2 {
            font-size: 18px;
            margin-bottom: 14px;
            color: #2c3e50;
        }

        /* ---- Card / Section Box ---- */
        .card {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        /* ---- Alert Messages ---- */
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

        /* ---- Form ---- */
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 5px; }
        .form-group input {
            width: 100%; padding: 9px 12px;
            border: 1px solid #ccc; border-radius: 5px; font-size: 14px;
        }
        .form-group input:focus { outline: none; border-color: #3498db; }
        .form-row { display: flex; gap: 16px; flex-wrap: wrap; }
        .form-row .form-group { flex: 1; min-width: 180px; }
        .btn {
            padding: 10px 22px; border: none; border-radius: 5px;
            cursor: pointer; font-size: 14px; font-weight: 600;
        }
        .btn-primary { background: #3498db; color: #fff; }
        .btn-primary:hover { background: #2980b9; }

        /* ---- Data Table ---- */
        table { width: 100%; border-collapse: collapse; }
        th {
            background: #2c3e50; color: #fff;
            padding: 11px 14px; text-align: left; font-size: 14px;
        }
        td { padding: 10px 14px; font-size: 14px; border-bottom: 1px solid #e9ecef; }
        tr:hover td { background: #f8f9fa; }
        .badge {
            background: #eaf4fd; color: #2980b9;
            padding: 3px 8px; border-radius: 12px; font-size: 12px;
        }

        /* ---- Action Buttons in table ---- */
        .btn-edit   { background: #f39c12; color:#fff; padding:5px 12px; border-radius:4px;
                      text-decoration:none; font-size:13px; font-weight:600; }
        .btn-edit:hover { background:#d68910; }
        .btn-delete { background: #e74c3c; color:#fff; padding:5px 12px; border-radius:4px;
                      text-decoration:none; font-size:13px; font-weight:600; }
        .btn-delete:hover { background:#c0392b; }

        /* ---- Footer ---- */
        .footer { text-align:center; margin-top:30px; color:#888; font-size:13px; }
    </style>
</head>
<body>
<div class="container">

    <!-- Page Header -->
    <h1>🎓 Student Management System — Practical 19</h1>

    <!-- =====================================================
         SECTION 1 : Add New Student Form
    ====================================================== -->
    <div class="card">
        <h2>➕ Add New Student</h2>

        <!-- Show success or error message if any -->
        <?php if ($success): ?>
            <div class="msg-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="msg-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Form posts to itself (index.php) -->
        <form method="POST" action="index.php">
            <div class="form-row">
                <!-- Name Field -->
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name"
                           placeholder="e.g. Alice Johnson"
                           value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                </div>
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email"
                           placeholder="e.g. alice@example.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <!-- Course Field -->
                <div class="form-group">
                    <label for="course">Course</label>
                    <input type="text" id="course" name="course"
                           placeholder="e.g. B.Tech Computer Science"
                           value="<?= htmlspecialchars($_POST['course'] ?? '') ?>">
                </div>
            </div>
            <!-- Hidden field to identify this form on submission -->
            <input type="hidden" name="add_student" value="1">
            <button type="submit" class="btn btn-primary">➕ Add Student</button>
        </form>
    </div>

    <!-- =====================================================
         SECTION 2 : Display All Students Table (READ)
    ====================================================== -->
    <div class="card">
        <h2>📋 All Students</h2>

        <?php if ($result && $result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Enrolled On</th>
                    <th>Edit</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <!-- fetch_assoc() returns one row at a time as an associative array -->
                <tr>
                    <td><?= $row['id'] ?></td>
                    <!-- htmlspecialchars() prevents XSS when displaying data -->
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($row['course']) ?></span></td>
                    <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                    <!-- Edit button: passes the student ID to edit.php -->
                    <td>
                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn-edit">✏️ Edit</a>
                    </td>
                    <!-- Delete button: passes ID to delete.php -->
                    <td>
                        <a href="delete.php?id=<?= $row['id'] ?>"
                           class="btn-delete"
                           onclick="return confirm('⚠️ Are you sure you want to delete <?= htmlspecialchars(addslashes($row['name'])) ?>?');">
                           🗑️ Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <!-- Shown when no rows exist yet -->
            <p style="color:#888;text-align:center;padding:20px;">
                No students found. Add one using the form above.
            </p>
        <?php endif; ?>
    </div>

    <div class="footer">Practical 19 &mdash; PHP &amp; MySQL CRUD Application</div>
</div>
</body>
</html>
<?php
// Close the database connection at the end
$conn->close();
?>
