<?php
$host     = "localhost";
$db_user  = "root";
$db_pass  = "";
$db_name  = "result_management_system";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$roll_number = $_GET['roll_number'] ?? '';

if (empty($roll_number)) {
    die("<h2 style='text-align:center;margin-top:50px;'>Invalid Request. Please enter a Roll Number.</h2>");
}

// Fetch all subject marks for the student
$stmt = $conn->prepare("SELECT m.*, s.program 
                        FROM marks m 
                        LEFT JOIN students s ON m.roll_number = s.roll_number 
                        WHERE m.roll_number = ? 
                        ORDER BY m.subject_code ASC");
$stmt->bind_param("s", $roll_number);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<div style='text-align:center;margin-top:50px;font-family:sans-serif;'>
            <h2>No Result Found!</h2>
            <p>No marks found for Roll Number: <strong>".htmlspecialchars($roll_number)."</strong></p>
            <a href='check_result.php'>Go Back</a>
         </div>");
}

$marks_data = [];
while ($row = $result->fetch_assoc()) {
    $marks_data[] = $row;
}
$stmt->close();

// Student Basic Info (from first row)
$student_name = $marks_data[0]['student_name'];
$program      = $marks_data[0]['program'] ?? 'B.Tech CS';

// Dynamic Calculations across all subjects
$total_subjects      = count($marks_data);
$grand_total_obtained = 0;
$grand_max_possible   = $total_subjects * 100;
$has_failed_subject   = false;

foreach ($marks_data as $sub) {
    $grand_total_obtained += $sub['total_marks'];
    
    // Pass Criteria: Har subject me at least 40 marks aur Grade 'F' na ho
    if ($sub['total_marks'] < 40 || $sub['grade'] === 'F') {
        $has_failed_subject = true;
    }
}

$overall_percentage = round(($grand_total_obtained / $grand_max_possible) * 100, 2);
$overall_cgpa       = round($overall_percentage / 9.5, 2);
$final_result       = ($has_failed_subject) ? 'FAILED' : 'PASSED';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Digital Marksheet - <?php echo htmlspecialchars($roll_number); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
  body { 
    font-family: 'Inter', sans-serif; 
    background: #f1f5f9; 
    padding: 40px 20px; 
    color: #0f172a; 
    margin: 0;
  }

  .marksheet {
    max-width: 850px;
    margin: 0 auto;
    background: #ffffff;
    padding: 40px;
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    box-sizing: border-box; /* Fixes width calculations */
  }

  .header { 
    text-align: center; 
    border-bottom: 2px solid #0f172a; 
    padding-bottom: 20px; 
    margin-bottom: 24px; 
  }
  .header h1 { margin: 0 0 6px 0; font-size: 1.6rem; text-transform: uppercase; letter-spacing: 1px; }
  .header p { margin: 0; color: #64748b; font-size: 0.9rem; }
  
  .student-info { 
    display: grid; 
    grid-template-columns: 1fr 1fr; 
    gap: 16px; 
    margin-bottom: 28px; 
    background: #f8fafc; 
    padding: 16px; 
    border-radius: 8px; 
  }
  .info-item span { display: block; font-size: 0.8rem; color: #64748b; }
  .info-item strong { font-size: 1rem; color: #0f172a; }

  /* TABLE CSS FIXES FOR CROP ISSUE */
  table { 
    width: 100%; 
    border-collapse: collapse; 
    margin-bottom: 28px; 
    table-layout: auto; /* Ensures columns fit nicely within boundaries */
  }
  
  th, td { 
    padding: 10px 12px; /* Slightly reduced padding so contents fit */
    border: 1px solid #e2e8f0; 
    text-align: left; 
    word-break: break-word; 
  }
  
  th { background: #f8fafc; font-size: 0.8rem; text-transform: uppercase; }

  /* Specific column adjustments */
  th:last-child, td:last-child {
    text-align: center;
    width: 80px; /* Gives explicit space to the Grade column */
  }

  .summary-grid { 
    display: grid; 
    grid-template-columns: repeat(4, 1fr); 
    gap: 12px; 
    margin-bottom: 28px; 
    text-align: center; 
  }
  .summary-card { 
    background: #f8fafc; 
    padding: 12px; 
    border-radius: 8px; 
    border: 1px solid #e2e8f0; 
  }
  .summary-card span { font-size: 0.75rem; color: #64748b; display: block; }
  .summary-card strong { font-size: 1.15rem; }

  .status-pass { color: #16a34a; }
  .status-fail { color: #dc2626; }
  .badge-f { color: #dc2626; font-weight: bold; }

  .actions { text-align: center; margin-top: 20px; }
  .btn-print { 
    background: #0f172a; 
    color: white; 
    border: none; 
    padding: 10px 24px; 
    border-radius: 6px; 
    cursor: pointer; 
    font-weight: 600; 
  }
  
  /* PERFECT PRINT STYLES FOR A4 */
  @media print {
    @page {
      size: A4 portrait;
      margin: 15mm; /* Sets clean page margins for print */
    }
    body { 
      background: white; 
      padding: 0; 
      margin: 0; 
    }
    .marksheet { 
      border: none; 
      box-shadow: none; 
      width: 100% !important; 
      max-width: 100% !important; 
      padding: 0; 
      margin: 0; 
    }
    .actions { display: none; }
  }
</style>
</head>
<body>

<div class="marksheet">
  <div class="header">
    <h1>Academic Grade Transcript</h1>
    <p>Session 2025–2026 | Official Digital Marksheet</p>
  </div>

  <div class="student-info">
    <div class="info-item">
      <span>Student Name</span>
      <strong><?php echo htmlspecialchars($student_name); ?></strong>
    </div>
    <div class="info-item">
      <span>Roll Number</span>
      <strong><?php echo htmlspecialchars($roll_number); ?></strong>
    </div>
    <div class="info-item">
      <span>Program</span>
      <strong><?php echo htmlspecialchars($program); ?></strong>
    </div>
    <div class="info-item">
      <span>Total Subjects</span>
      <strong><?php echo $total_subjects; ?></strong>
    </div>
  </div>

  <!-- Extended Dynamic Subjects Table -->
  <table>
    <thead>
      <tr>
        <th>Subject Code</th>
        <th>Subject Name</th>
        <th>Internal (30)</th>
        <th>External (70)</th>
        <th>Total Marks</th>
        <th>Grade</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($marks_data as $sub): ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($sub['subject_code']); ?></strong></td>
          <td><?php echo htmlspecialchars($sub['subject_name']); ?></td>
          <td><?php echo $sub['internal_marks']; ?></td>
          <td><?php echo $sub['external_marks']; ?></td>
          <td><strong><?php echo $sub['total_marks']; ?></strong></td>
          <td class="<?php echo ($sub['grade'] === 'F') ? 'badge-f' : ''; ?>">
            <strong><?php echo $sub['grade']; ?></strong>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Dynamic Summary Calculation -->
  <div class="summary-grid">
    <div class="summary-card">
      <span>Grand Total</span>
      <strong><?php echo $grand_total_obtained; ?> / <?php echo $grand_max_possible; ?></strong>
    </div>
    <div class="summary-card">
      <span>Overall Percentage</span>
      <strong><?php echo $overall_percentage; ?>%</strong>
    </div>
    <div class="summary-card">
      <span>Overall CGPA</span>
      <strong><?php echo $overall_cgpa; ?></strong>
    </div>
    <div class="summary-card">
      <span>Final Result</span>
      <strong class="<?php echo ($final_result === 'PASSED') ? 'status-pass' : 'status-fail'; ?>">
        <?php echo $final_result; ?>
      </strong>
    </div>
  </div>

  <div class="actions">
    <button onclick="window.print()" class="btn-print">Print / Download PDF</button>
  </div>
</div>

</body>
</html>