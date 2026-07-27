<?php
// ==========================================
// 1. DATABASE CONNECTION
// ==========================================
$host     = "localhost";
$db_user  = "root";
$db_pass  = "";
$db_name  = "result_management_system";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$message = "";

// Grade Logic Helper Function
function calculateGrade($total) {
    if ($total >= 90) return 'A+';
    if ($total >= 80) return 'A';
    if ($total >= 60) return 'B';
    if ($total >= 40) return 'C';
    return 'F';
}

// Badge CSS Class Helper Function
function getBadgeClass($grade) {
    switch ($grade) {
        case 'A+': return 'badge-aplus';
        case 'A':  return 'badge-a';
        case 'B':  return 'badge-b';
        case 'C':  return 'badge-c';
        default:   return 'badge-f';
    }
}

// ==========================================
// 2. SAVE / UPDATE MARKS IN DATABASE
// ==========================================
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['students'])) {

    $stmt = $conn->prepare("INSERT INTO marks (roll_number, student_name, internal_marks, external_marks, total_marks, grade, remarks) 
                            VALUES (?, ?, ?, ?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE 
                            student_name   = VALUES(student_name),
                            internal_marks = VALUES(internal_marks), 
                            external_marks = VALUES(external_marks), 
                            total_marks    = VALUES(total_marks),
                            grade          = VALUES(grade),
                            remarks        = VALUES(remarks)");

    foreach ($_POST['students'] as $student) {
        $roll_number  = $student['roll_number'];
        $student_name = $student['student_name'];
        $internal     = ($student['internal'] === '') ? 0 : intval($student['internal']);
        $external     = ($student['external'] === '') ? 0 : intval($student['external']);
        $remarks      = trim($student['remarks'] ?? '');
        
        $total        = $internal + $external;
        $grade        = calculateGrade($total);

        // Bind parameters: 'ssiiiss' -> string, string, int, int, int, string, string
        $stmt->bind_param("ssiiiss", $roll_number, $student_name, $internal, $external, $total, $grade, $remarks);
        $stmt->execute();
    }

    $stmt->close();
    $message = "Marks and grades successfully saved!";
}

// ==========================================
// 3. FETCH DATA (STUDENTS LEFT JOIN MARKS)
// ==========================================
$sql = "SELECT s.roll_number, s.student_name, 
               m.internal_marks, m.external_marks, m.total_marks, m.grade, m.remarks 
        FROM students s 
        LEFT JOIN marks m ON s.roll_number = m.roll_number 
        ORDER BY s.roll_number ASC";

$result = $conn->query($sql);
$students = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Marks & Grade Entry - Teacher Portal</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <style>
    :root {
      --bg-body: #f8fafc;
      --bg-card: #ffffff;
      --border-card: #e2e8f0;
      --text-dark: #0f172a;
      --text-muted: #64748b;
      --accent-blue: #2563eb;
      --accent-blue-hover: #1d4ed8;
      --radius-card: 12px;
      --radius-sm: 6px;
      --shadow-subtle: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg-body);
      color: var(--text-dark);
      margin: 0;
      padding: 0;
    }

    .page-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 24px;
    }

    /* HEADER */
    .top-header {
      background: #1e293b;
      color: white;
      padding: 16px 24px;
      border-radius: var(--radius-card);
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
    }

    .header-left { display: flex; align-items: center; gap: 16px; }
    .brand-title { margin: 0; font-size: 1.25rem; font-weight: 700; }
    .brand-sub { font-size: 0.8rem; color: #94a3b8; }

    /* FILTERS */
    .filter-card {
      background: var(--bg-card);
      border: 1px solid var(--border-card);
      border-radius: var(--radius-card);
      padding: 20px;
      margin-bottom: 24px;
      box-shadow: var(--shadow-subtle);
    }

    .filter-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
    }

    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group label { font-size: 0.85rem; font-weight: 600; color: var(--text-dark); }
    .form-control {
      padding: 10px 14px;
      border: 1px solid var(--border-card);
      border-radius: var(--radius-sm);
      font-size: 0.9rem;
      outline: none;
    }

    /* TABLE */
    .table-container {
      background: var(--bg-card);
      border: 1px solid var(--border-card);
      border-radius: var(--radius-card);
      box-shadow: var(--shadow-subtle);
      overflow: hidden;
      margin-bottom: 24px;
    }

    .marks-table { width: 100%; border-collapse: collapse; text-align: left; }
    .marks-table th {
      background-color: #f8fafc;
      color: var(--text-dark);
      padding: 14px 16px;
      font-size: 0.8rem;
      font-weight: 700;
      border-bottom: 1px solid var(--border-card);
      text-transform: uppercase;
    }

    .marks-table td {
      padding: 14px 16px;
      border-bottom: 1px solid #f1f5f9;
      vertical-align: middle;
      font-size: 0.9rem;
    }

    .input-mark {
      width: 70px;
      padding: 8px;
      border: 1px solid var(--border-card);
      border-radius: 6px;
      font-size: 0.9rem;
      font-weight: 600;
      text-align: center;
    }

    /* BADGES */
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 700;
    }
    .badge-aplus { background: #dcfce7; color: #15803d; }
    .badge-a { background: #e0f2fe; color: #0369a1; }
    .badge-b { background: #fef9c3; color: #a16207; }
    .badge-c { background: #ffedd5; color: #c2410c; }
    .badge-f { background: #ffe4e6; color: #be123c; }

    .alert-success {
      background: #dcfce7;
      color: #15803d;
      border: 1px solid #bbf7d0;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-weight: 600;
    }

    /* BUTTONS */
    .btn-container { display: flex; justify-content: flex-end; gap: 16px; }
    .btn-primary {
      background-color: var(--accent-blue);
      color: #ffffff;
      border: none;
      padding: 12px 28px;
      border-radius: var(--radius-sm);
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
    }
    .btn-primary:hover { background-color: var(--accent-blue-hover); }
    .btn-secondary {
      background-color: #e2e8f0;
      color: var(--text-dark);
      border: none;
      padding: 12px 24px;
      border-radius: var(--radius-sm);
      font-size: 0.95rem;
      font-weight: 600;
      cursor: pointer;
    }

    @media (max-width: 900px) {
      .filter-grid { grid-template-columns: 1fr 1fr; }
      .table-container { overflow-x: auto; }
    }
  </style>
</head>
<body>

  <div class="page-container">
    
    <!-- TOP HEADER -->
    <header class="top-header">
      <div class="header-left">
        <div class="brand-info">
          <h1 class="brand-title">Marks & Grade Entry</h1>
          <span class="brand-sub">Academic Assessment & Evaluation Module</span>
        </div>
      </div>
      <div>
        <span style="font-size: 0.85rem; color: #cbd5e1;">Instructor: <strong>Prof. Anderson</strong></span>
      </div>
    </header>

    <?php if (!empty($message)): ?>
      <div class="alert-success">✓ <?php echo $message; ?></div>
    <?php endif; ?>

    <form action="/RMS/result_page.php" method="POST">

      <!-- FILTERS -->
      <div class="filter-card">
        <div class="filter-grid">
          <div class="form-group">
            <label for="program">Program / Degree</label>
            <select id="program" class="form-control">
              <option>B.Tech Computer Science</option>
              <option>B.Sc Data Science</option>
            </select>
          </div>

          <div class="form-group">
            <label for="semester">Semester</label>
            <select id="semester" class="form-control">
              <option>Semester VI (Spring 2026)</option>
              <option>Semester V (Fall 2025)</option>
            </select>
          </div>

          <div class="form-group">
            <label for="course">Course / Subject</label>
            <select id="course" class="form-control">
              <option>CS-601: Database Management Systems</option>
              <option>CS-602: Web Technologies</option>
            </select>
          </div>

          <div class="form-group">
            <label for="exam-type">Assessment Type</label>
            <select id="exam-type" class="form-control">
              <option>End-Semester Examination (100 Marks)</option>
            </select>
          </div>
        </div>
      </div>

      <!-- DYNAMIC TABLE -->
      <div class="table-container">
        <table class="marks-table">
          <thead>
            <tr>
              <th>Roll Number</th>
              <th>Student Name</th>
              <th>Internal (30)</th>
              <th>External (70)</th>
              <th>Total (100)</th>
              <th>Grade</th>
              <th>Remarks</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($students)): ?>
              <?php foreach ($students as $i => $s): ?>
                <?php 
                  $internal = $s['internal_marks'] ?? '';
                  $external = $s['external_marks'] ?? '';
                  $total    = ($s['total_marks'] !== null) ? $s['total_marks'] : '—';
                  $grade    = $s['grade'] ?? '—';
                  $badge    = ($grade !== '—') ? getBadgeClass($grade) : 'badge-f';
                ?>
                <tr>
                  <td>
                    <strong><?php echo htmlspecialchars($s['roll_number']); ?></strong>
                    <input type="hidden" name="students[<?php echo $i; ?>][roll_number]" value="<?php echo htmlspecialchars($s['roll_number']); ?>">
                  </td>
                  <td>
                    <?php echo htmlspecialchars($s['student_name']); ?>
                    <input type="hidden" name="students[<?php echo $i; ?>][student_name]" value="<?php echo htmlspecialchars($s['student_name']); ?>">
                  </td>
                  <td>
                    <input type="number" name="students[<?php echo $i; ?>][internal]" class="input-mark" placeholder="0" min="0" max="30" value="<?php echo htmlspecialchars($internal); ?>">
                  </td>
                  <td>
                    <input type="number" name="students[<?php echo $i; ?>][external]" class="input-mark" placeholder="0" min="0" max="70" value="<?php echo htmlspecialchars($external); ?>">
                  </td>
                  <td><strong><?php echo $total; ?></strong></td>
                  <td>
                    <?php if ($grade !== '—'): ?>
                      <span class="badge <?php echo $badge; ?>"><?php echo $grade; ?></span>
                    <?php else: ?>
                      —
                    <?php endif; ?>
                  </td>
                  <td>
                    <input type="text" name="students[<?php echo $i; ?>][remarks]" class="form-control" placeholder="Optional notes..." value="<?php echo htmlspecialchars($s['remarks'] ?? ''); ?>">
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" style="text-align: center; color: #64748b; padding: 24px;">No students found in the database. Run the SQL script above to add sample records.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- FORM ACTIONS -->
      <div class="btn-container">
        <button class="btn-secondary" type="submit">Save Draft</button>
        <button class="btn-primary" type="submit">Submit Final Grades</button>
      </div>

    </form>

    <!-- FOOTER -->
    <footer style="margin-top: 40px; text-align: center; color: #94a3b8; font-size: 0.85rem;">
      <p>&copy; 2026 Result Management System. Faculty Operations Control Center.</p>
    </footer>

  </div>

</body>
</html>