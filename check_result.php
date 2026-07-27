<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Check Academic Result</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
      background-color: #f8fafc;
      color: #0f172a;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      margin: 0;
    }
    .card {
      background: #ffffff;
      padding: 40px;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
      width: 100%;
      max-width: 420px;
      text-align: center;
    }
    .card-title {
      font-size: 1.5rem;
      font-weight: 700;
      margin-bottom: 8px;
    }
    .card-sub {
      color: #64748b;
      font-size: 0.9rem;
      margin-bottom: 28px;
    }
    .form-group {
      text-align: left;
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 8px;
      color: #334155;
    }
    .form-control {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      font-size: 1rem;
      box-sizing: border-box;
      outline: none;
      transition: border-color 0.2s;
    }
    .form-control:focus {
      border-color: #2563eb;
      box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }
    .btn-submit {
      width: 100%;
      background-color: #2563eb;
      color: white;
      border: none;
      padding: 14px;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.2s;
    }
    .btn-submit:hover {
      background-color: #1d4ed8;
    }
  </style>
</head>
<body>

  <div class="card">
    <h1 class="card-title">Student Result Portal</h1>
    <p class="card-sub">Enter your credentials to view your digital grade card</p>

    <form action="marksheet.php" method="GET">
      <div class="form-group">
        <label for="roll_number">Enter Roll Number</label>
        <input type="text" id="roll_number" name="roll_number" class="form-control" placeholder="e.g. 2026-CS-001" required>
      </div>

      <button type="submit" class="btn-submit">See Result</button>
    </form>
  </div>

</body>
</html>