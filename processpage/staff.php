<?php
session_start();

// Initialize employee data in session if not exists
if (!isset($_SESSION['employees'])) {
    $_SESSION['employees'] = [
        1 => [
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@company.com',
            'phone' => '555-0123',
            'department' => 'IT',
            'position' => 'Developer',
            'salary' => 75000,
            'hire_date' => '2023-01-15'
        ],
        2 => [
            'id' => 2,
            'name' => 'Jane Smith',
            'email' => 'jane@company.com',
            'phone' => '555-0456',
            'department' => 'HR',
            'position' => 'Manager',
            'salary' => 85000,
            'hire_date' => '2022-08-20'
        ]
    ];
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $id = count($_SESSION['employees']) + 1;
                $_SESSION['employees'][$id] = [
                    'id' => $id,
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'phone' => $_POST['phone'],
                    'department' => $_POST['department'],
                    'position' => $_POST['position'],
                    'salary' => $_POST['salary'],
                    'hire_date' => $_POST['hire_date']
                ];
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $_SESSION['employees'][$id] = [
                    'id' => $id,
                    'name' => $_POST['name'],
                    'email' => $_POST['email'],
                    'phone' => $_POST['phone'],
                    'department' => $_POST['department'],
                    'position' => $_POST['position'],
                    'salary' => $_POST['salary'],
                    'hire_date' => $_POST['hire_date']
                ];
                break;
                
            case 'delete':
                $id = $_POST['id'];
                unset($_SESSION['employees'][$id]);
                break;
        }
    }
}

$current_page = $_GET['page'] ?? 'dashboard';
$edit_employee = null;

if ($current_page === 'edit' && isset($_GET['id'])) {
    $edit_employee = $_SESSION['employees'][$_GET['id']] ?? null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }

        .sidebar h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 1.5em;
        }

        .sidebar ul {
            list-style: none;
        }

        .sidebar li {
            margin: 10px 0;
        }

        .sidebar a {
            display: block;
            padding: 12px 20px;
            color: white;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: rgba(255,255,255,0.2);
        }

        .main-content {
            flex: 1;
            padding: 30px;
        }

        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-card h3 {
            color: #666;
            margin-bottom: 10px;
        }

        .stat-card .number {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
        }

        .content-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background: #5a6fd8;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-success {
            background: #28a745;
        }

        .btn-success:hover {
            background: #218838;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .table tr:hover {
            background-color: #f5f5f5;
        }

        .search-box {
            width: 300px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d1edff;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                order: 2;
            }
            
            .main-content {
                order: 1;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <h2>EMS Admin</h2>
            <ul>
                <li><a href="?page=dashboard" class="<?= $current_page === 'dashboard' ? 'active' : '' ?>">📊 Dashboard</a></li>
                <li><a href="?page=employees" class="<?= $current_page === 'employees' ? 'active' : '' ?>">👥 Employees</a></li>
                <li><a href="?page=add" class="<?= $current_page === 'add' ? 'active' : '' ?>">➕ Add Employee</a></li>
                <li><a href="?page=departments" class="<?= $current_page === 'departments' ? 'active' : '' ?>">🏢 Departments</a></li>
                <li><a href="?page=reports" class="<?= $current_page === 'reports' ? 'active' : '' ?>">📈 Reports</a></li>
            </ul>
        </div>

        <div class="main-content">
            <?php if ($current_page === 'dashboard'): ?>
                <div class="header">
                    <h1>Dashboard</h1>
                    <p>Welcome to Employee Management System</p>
                </div>

                <div class="stats">
                    <div class="stat-card">
                        <h3>Total Employees</h3>
                        <div class="number"><?= count($_SESSION['employees']) ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Departments</h3>
                        <div class="number"><?= count(array_unique(array_column($_SESSION['employees'], 'department'))) ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Average Salary</h3>
                        <div class="number">$<?= number_format(array_sum(array_column($_SESSION['employees'], 'salary')) / count($_SESSION['employees'])) ?></div>
                    </div>
                </div>

                <div class="content-card">
                    <h2>Recent Employees</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Hire Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($_SESSION['employees'], -5, 5, true) as $employee): ?>
                                <tr>
                                    <td><?= htmlspecialchars($employee['name']) ?></td>
                                    <td><?= htmlspecialchars($employee['department']) ?></td>
                                    <td><?= htmlspecialchars($employee['position']) ?></td>
                                    <td><?= htmlspecialchars($employee['hire_date']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($current_page === 'employees'): ?>
                <div class="header">
                    <h1>Employee Management</h1>
                    <p>Manage all employee records</p>
                </div>

                <div class="content-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <input type="text" class="search-box" placeholder="Search employees..." id="searchInput">
                        <a href="?page=add" class="btn btn-success">Add New Employee</a>
                    </div>

                    <table class="table" id="employeeTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Salary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($_SESSION['employees'] as $employee): ?>
                                <tr>
                                    <td><?= $employee['id'] ?></td>
                                    <td><?= htmlspecialchars($employee['name']) ?></td>
                                    <td><?= htmlspecialchars($employee['email']) ?></td>
                                    <td><?= htmlspecialchars($employee['phone']) ?></td>
                                    <td><?= htmlspecialchars($employee['department']) ?></td>
                                    <td><?= htmlspecialchars($employee['position']) ?></td>
                                    <td>$<?= number_format($employee['salary']) ?></td>
                                    <td>
                                        <a href="?page=edit&id=<?= $employee['id'] ?>" class="btn">Edit</a>
                                        <form method="post" style="display: inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $employee['id'] ?>">
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($current_page === 'add' || $current_page === 'edit'): ?>
                <div class="header">
                    <h1><?= $current_page === 'add' ? 'Add New Employee' : 'Edit Employee' ?></h1>
                    <p><?= $current_page === 'add' ? 'Add a new employee to the system' : 'Update employee information' ?></p>
                </div>

                <div class="content-card">
                    <form method="post">
                        <input type="hidden" name="action" value="<?= $current_page === 'add' ? 'add' : 'edit' ?>">
                        <?php if ($current_page === 'edit'): ?>
                            <input type="hidden" name="id" value="<?= $edit_employee['id'] ?>">
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?= $edit_employee['name'] ?? '' ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" value="<?= $edit_employee['email'] ?? '' ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?= $edit_employee['phone'] ?? '' ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select id="department" name="department" required>
                                    <option value="">Select Department</option>
                                    <option value="IT" <?= ($edit_employee['department'] ?? '') === 'IT' ? 'selected' : '' ?>>IT</option>
                                    <option value="HR" <?= ($edit_employee['department'] ?? '') === 'HR' ? 'selected' : '' ?>>HR</option>
                                    <option value="Finance" <?= ($edit_employee['department'] ?? '') === 'Finance' ? 'selected' : '' ?>>Finance</option>
                                    <option value="Marketing" <?= ($edit_employee['department'] ?? '') === 'Marketing' ? 'selected' : '' ?>>Marketing</option>
                                    <option value="Sales" <?= ($edit_employee['department'] ?? '') === 'Sales' ? 'selected' : '' ?>>Sales</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="position">Position</label>
                                <input type="text" id="position" name="position" value="<?= $edit_employee['position'] ?? '' ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="salary">Salary</label>
                                <input type="number" id="salary" name="salary" value="<?= $edit_employee['salary'] ?? '' ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="hire_date">Hire Date</label>
                            <input type="date" id="hire_date" name="hire_date" value="<?= $edit_employee['hire_date'] ?? '' ?>" required>
                        </div>

                        <button type="submit" class="btn btn-success"><?= $current_page === 'add' ? 'Add Employee' : 'Update Employee' ?></button>
                        <a href="?page=employees" class="btn">Cancel</a>
                    </form>
                </div>

            <?php elseif ($current_page === 'departments'): ?>
                <div class="header">
                    <h1>Departments</h1>
                    <p>Department overview and statistics</p>
                </div>

                <div class="content-card">
                    <h2>Department Statistics</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Employee Count</th>
                                <th>Average Salary</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $departments = [];
                            foreach ($_SESSION['employees'] as $employee) {
                                $dept = $employee['department'];
                                if (!isset($departments[$dept])) {
                                    $departments[$dept] = ['count' => 0, 'total_salary' => 0];
                                }
                                $departments[$dept]['count']++;
                                $departments[$dept]['total_salary'] += $employee['salary'];
                            }
                            
                            foreach ($departments as $dept => $data):
                                $avg_salary = $data['total_salary'] / $data['count'];
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($dept) ?></td>
                                    <td><?= $data['count'] ?></td>
                                    <td>$<?= number_format($avg_salary) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($current_page === 'reports'): ?>
                <div class="header">
                    <h1>Reports</h1>
                    <p>Employee reports and analytics</p>
                </div>

                <div class="content-card">
                    <h2>Employee Report</h2>
                    <p>Total Employees: <strong><?= count($_SESSION['employees']) ?></strong></p>
                    <p>Total Salary Expense: <strong>$<?= number_format(array_sum(array_column($_SESSION['employees'], 'salary'))) ?></strong></p>
                    
                    <h3>Salary Distribution</h3>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Salary Range</th>
                                <th>Employee Count</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ranges = [
                                '< $50,000' => 0,
                                '$50,000 - $70,000' => 0,
                                '$70,000 - $90,000' => 0,
                                '> $90,000' => 0
                            ];
                            
                            foreach ($_SESSION['employees'] as $employee) {
                                $salary = $employee['salary'];
                                if ($salary < 50000) $ranges['< $50,000']++;
                                elseif ($salary <= 70000) $ranges['$50,000 - $70,000']++;
                                elseif ($salary <= 90000) $ranges['$70,000 - $90,000']++;
                                else $ranges['> $90,000']++;
                            }
                            
                            foreach ($ranges as $range => $count):
                            ?>
                                <tr>
                                    <td><?= $range ?></td>
                                    <td><?= $count ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput')?.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('employeeTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length - 1; j++) {
                    if (cells[j].textContent.toLowerCase().includes(searchTerm)) {
                        found = true;
                        break;
                    }
                }
                
                row.style.display = found ? '' : 'none';
            }
        });

        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('input[required], select[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.style.borderColor = '#dc3545';
                    } else {
                        field.style.borderColor = '#ddd';
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields');
                }
            });
        });
    </script>
</body>
</html>
