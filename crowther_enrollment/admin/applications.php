<?php
// admin/applications.php - Manage all applications
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications | Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6f9;
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 260px;
            height: 100%;
            background: #1a3c5e;
            color: white;
            padding: 1.5rem;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #ffc107;
            padding-bottom: 0.5rem;
        }
        .sidebar nav ul {
            list-style: none;
        }
        .sidebar nav ul li {
            margin-bottom: 0.5rem;
        }
        .sidebar nav ul li a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            border-radius: 8px;
        }
        .sidebar nav ul li a:hover,
        .sidebar nav ul li a.active {
            background: #ffc107;
            color: #1a3c5e;
        }
        .sidebar nav ul li a i {
            margin-right: 10px;
        }
        .main-content {
            margin-left: 260px;
            padding: 2rem;
        }
        .top-header {
            background: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
        }
        .filter-bar {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        .filter-bar input, .filter-bar select {
            padding: 8px;
            margin: 0 10px 0 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #f4f6f9;
        }
        .status-pending { background: #ffc107; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; }
        .status-approved { background: #28a745; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; }
        .status-rejected { background: #dc3545; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; }
        .action-btn {
            background: #1a3c5e;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 0.8rem;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2><i class="fas fa-school"></i> CMC Admin</h2>
    <nav>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="applications.php" class="active"><i class="fas fa-file-alt"></i> Applications</a></li>
            <li><a href="students.php"><i class="fas fa-users"></i> Students</a></li>
            <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            <li><a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
        </ul>
    </nav>
</div>

<div class="main-content">
    <div class="top-header">
        <h2><i class="fas fa-file-alt"></i> All Applications</h2>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
    
    <div class="filter-bar">
        <label>Filter by Status:</label>
        <select>
            <option>All</option>
            <option>Pending</option>
            <option>Approved</option>
            <option>Rejected</option>
        </select>
        <label>Search:</label>
        <input type="text" placeholder="Student name or ID">
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Application ID</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>Parent</th>
                <th>Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>#CMC001</td>
                <td>James Okafor</td>
                <td>JSS 1</td>
                <td>Mr. Peter Okafor</td>
                <td>2026-04-28</td>
                <td><span class="status-pending">Pending</span></td>
                <td><a href="#" class="action-btn">Approve</a> <a href="#" class="action-btn">Reject</a> <a href="#" class="action-btn">View</a></td>
            </tr>
            <tr>
                <td>#CMC002</td>
                <td>Grace Musa</td>
                <td>Primary 4</td>
                <td>Mrs. Faith Musa</td>
                <td>2026-04-27</td>
                <td><span class="status-approved">Approved</span></td>
                <td><a href="#" class="action-btn">View</a></td>
            </tr>
            <tr>
                <td>#CMC003</td>
                <td>Samuel Ade</td>
                <td>SSS 1</td>
                <td>Mr. John Ade</td>
                <td>2026-04-26</td>
                <td><span class="status-pending">Pending</span></td>
                <td><a href="#" class="action-btn">Approve</a> <a href="#" class="action-btn">Reject</a> <a href="#" class="action-btn">View</a></td>
            </tr>
        </tbody>
    </table>
</div>

</body>
</html>