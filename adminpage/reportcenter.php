<?php
session_start();

// Simple login check
if (!isset($_SESSION['user'])) {
    header('Location: adminmain.php');
    exit();
}

include("../homepage/functions.php");
include('../homepage/db.php');

// // Date range filter (default to last 30 days)
// $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
// $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// // Visa Submissions Analytics
// function getVisaAnalytics($con, $start_date, $end_date) {
//     $analytics = [];
    
//     // Total submissions
//     $total_query = "SELECT COUNT(*) as total FROM visa_submissions WHERE DATE(submission_date) BETWEEN ? AND ?";
//     $stmt = mysqli_prepare($con, $total_query);
//     mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['total'] = mysqli_fetch_assoc($result)['total'];
    
//     // By status
//     $status_query = "SELECT status, COUNT(*) as count FROM visa_submissions WHERE DATE(submission_date) BETWEEN ? AND ? GROUP BY status";
//     $stmt = mysqli_prepare($con, $status_query);
//     mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['by_status'] = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $analytics['by_status'][$row['status']] = $row['count'];
//     }
    
//     // By destination
//     $dest_query = "SELECT destination, COUNT(*) as count FROM visa_submissions WHERE DATE(submission_date) BETWEEN ? AND ? GROUP BY destination ORDER BY count DESC LIMIT 5";
//     $stmt = mysqli_prepare($con, $dest_query);
//     mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['by_destination'] = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $analytics['by_destination'][] = $row;
//     }
    
//     // Daily submissions (last 7 days)
//     $daily_query = "SELECT DATE(submission_date) as date, COUNT(*) as count FROM visa_submissions WHERE DATE(submission_date) BETWEEN DATE_SUB(?, INTERVAL 7 DAY) AND ? GROUP BY DATE(submission_date) ORDER BY date";
//     $stmt = mysqli_prepare($con, $daily_query);
//     mysqli_stmt_bind_param($stmt, "ss", $end_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['daily'] = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $analytics['daily'][] = $row;
//     }
    
//     return $analytics;
// }

// // Employee Attendance Analytics
// function getAttendanceAnalytics($con, $start_date, $end_date) {
//     $analytics = [];
    
//     // Total attendance records
//     $total_query = "SELECT COUNT(*) as total FROM employee_attendance WHERE work_date BETWEEN ? AND ?";
//     $stmt = mysqli_prepare($con, $total_query);
//     mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['total_records'] = mysqli_fetch_assoc($result)['total'];
    
//     // Attendance by status
//     $status_query = "SELECT status, COUNT(*) as count FROM employee_attendance WHERE work_date BETWEEN ? AND ? GROUP BY status";
//     $stmt = mysqli_prepare($con, $status_query);
//     mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['by_status'] = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $analytics['by_status'][$row['status']] = $row['count'];
//     }
    
//     // Total hours worked
//     $hours_query = "SELECT SUM(hours_worked) as total_hours, AVG(hours_worked) as avg_hours FROM employee_attendance WHERE work_date BETWEEN ? AND ? AND status != 'absent'";
//     $stmt = mysqli_prepare($con, $hours_query);
//     mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $hours_data = mysqli_fetch_assoc($result);
//     $analytics['total_hours'] = round($hours_data['total_hours'], 2);
//     $analytics['avg_hours'] = round($hours_data['avg_hours'], 2);
    
//     // Top employees by hours
//     $top_query = "SELECT employee_name, SUM(hours_worked) as total_hours FROM employee_attendance WHERE work_date BETWEEN ? AND ? GROUP BY employee_id, employee_name ORDER BY total_hours DESC LIMIT 5";
//     $stmt = mysqli_prepare($con, $top_query);
//     mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['top_employees'] = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $analytics['top_employees'][] = $row;
//     }
    
//     return $analytics;
// }

// // Task Analytics
// function getTaskAnalytics($con, $start_date, $end_date) {
//     $analytics = [];
    
//     // Total tasks
//     $total_query = "SELECT COUNT(*) as total FROM tasks WHERE DATE(created_at) BETWEEN ? AND ?";
//     $stmt = mysqli_prepare($con, $total_query);
//     mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['total'] = mysqli_fetch_assoc($result)['total'];
    
//     // By status
//     $status_query = "SELECT status, COUNT(*) as count FROM tasks GROUP BY status";
//     $stmt = mysqli_prepare($con, $status_query);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['by_status'] = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $analytics['by_status'][$row['status']] = $row['count'];
//     }
    
//     // By priority
//     $priority_query = "SELECT priority, COUNT(*) as count FROM tasks GROUP BY priority";
//     $stmt = mysqli_prepare($con, $priority_query);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['by_priority'] = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $analytics['by_priority'][$row['priority']] = $row['count'];
//     }
    
//     // Overdue tasks
//     $overdue_query = "SELECT COUNT(*) as count FROM tasks WHERE due_date < CURDATE() AND status NOT IN ('completed')";
//     $stmt = mysqli_prepare($con, $overdue_query);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['overdue'] = mysqli_fetch_assoc($result)['count'];
    
//     // Tasks by assignee
//     $assignee_query = "SELECT assigned_to, COUNT(*) as count FROM tasks WHERE assigned_to IS NOT NULL GROUP BY assigned_to ORDER BY count DESC LIMIT 5";
//     $stmt = mysqli_prepare($con, $assignee_query);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['by_assignee'] = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $analytics['by_assignee'][] = $row;
//     }
    
//     return $analytics;
// }

// // Post Analytics
// function getPostAnalytics($con, $start_date, $end_date) {
//     $analytics = [];
    
//     // Total page views
//     $views_query = "SELECT COUNT(*) as total_views, COUNT(DISTINCT visitor_ip) as unique_visitors FROM post_analytics WHERE DATE(visit_date) BETWEEN ? AND ?";
//     $stmt = mysqli_prepare($con, $views_query);
//     mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $views_data = mysqli_fetch_assoc($result);
//     $analytics['total_views'] = $views_data['total_views'];
//     $analytics['unique_visitors'] = $views_data['unique_visitors'];
    
//     // Popular pages
//     $pages_query = "SELECT page_url, page_title, COUNT(*) as views FROM post_analytics WHERE DATE(visit_date) BETWEEN ? AND ? GROUP BY page_url, page_title ORDER BY views DESC LIMIT 10";
//     $stmt = mysqli_prepare($con, $pages_query);
//     mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['popular_pages'] = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $analytics['popular_pages'][] = $row;
//     }
    
//     // Traffic by device
//     $device_query = "SELECT device_type, COUNT(*) as count FROM post_analytics WHERE DATE(visit_date) BETWEEN ? AND ? GROUP BY device_type";
//     $stmt = mysqli_prepare($con, $device_query);
//     mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['by_device'] = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $analytics['by_device'][$row['device_type']] = $row['count'];
//     }
    
//     // Traffic by country
//     $country_query = "SELECT country, COUNT(*) as count FROM post_analytics WHERE DATE(visit_date) BETWEEN ? AND ? AND country IS NOT NULL GROUP BY country ORDER BY count DESC LIMIT 5";
//     $stmt = mysqli_prepare($con, $country_query);
//     mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['by_country'] = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $analytics['by_country'][] = $row;
//     }
    
//     // Daily traffic (last 7 days)
//     $daily_query = "SELECT DATE(visit_date) as date, COUNT(*) as views, COUNT(DISTINCT visitor_ip) as unique_visitors FROM post_analytics WHERE DATE(visit_date) BETWEEN DATE_SUB(?, INTERVAL 7 DAY) AND ? GROUP BY DATE(visit_date) ORDER BY date";
//     $stmt = mysqli_prepare($con, $daily_query);
//     mysqli_stmt_bind_param($stmt, "ss", $end_date, $end_date);
//     mysqli_stmt_execute($stmt);
//     $result = mysqli_stmt_get_result($stmt);
//     $analytics['daily_traffic'] = [];
//     while ($row = mysqli_fetch_assoc($result)) {
//         $analytics['daily_traffic'][] = $row;
//     }
    
//     return $analytics;
// }

// // Get all analytics data
// $visa_analytics = getVisaAnalytics($con, $start_date, $end_date);
// $attendance_analytics = getAttendanceAnalytics($con, $start_date, $end_date);
// $task_analytics = getTaskAnalytics($con, $start_date, $end_date);
// $post_analytics = getPostAnalytics($con, $start_date, $end_date);
// ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Report Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-header {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            color: black;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        .stats-label {
            color: #666;
            font-size: 0.9rem;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .section-title {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .filter-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light">
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1>Report Center</h1>
                </div>
                <div class="col-md-6 text-end">
                    <a href="adminmain.php" class="btn"><i class="fas fa-arrow-left"></i> Main Admin</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Date Filter -->
        <div class="filter-form">
            <form method="GET" class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter Reports</button>
                </div>
            </form>
        </div>

        <!-- Overview Stats -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $visa_analytics['total']; ?></div>
                    <div class="stats-label"><i class="fas fa-passport"></i> Visa Submissions</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $attendance_analytics['total_records']; ?></div>
                    <div class="stats-label"><i class="fas fa-clock"></i> Attendance Records</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $task_analytics['total']; ?></div>
                    <div class="stats-label"><i class="fas fa-tasks"></i> Tasks Created</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $post_analytics['total_views']; ?></div>
                    <div class="stats-label"><i class="fas fa-eye"></i> Page Views</div>
                </div>
            </div>
        </div>

        <!-- Visa Analytics Section -->
        <h2 class="section-title"><i class="fas fa-passport"></i> Visa Submission Analytics</h2>
        <div class="row mb-5">
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Submissions by Status</h5>
                    <canvas id="visaStatusChart" width="400" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Popular Destinations</h5>
                    <canvas id="destinationChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Attendance Analytics Section -->
        <h2 class="section-title"><i class="fas fa-clock"></i> Employee Attendance Analytics</h2>
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $attendance_analytics['total_hours'] ?? 0; ?></div>
                    <div class="stats-label">Total Hours Worked</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $attendance_analytics['avg_hours'] ?? 0; ?></div>
                    <div class="stats-label">Average Hours/Day</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo ($attendance_analytics['by_status']['present'] ?? 0) + ($attendance_analytics['by_status']['late'] ?? 0); ?></div>
                    <div class="stats-label">Days Present</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Attendance by Status</h5>
                    <canvas id="attendanceStatusChart" width="400" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Top Employees by Hours</h5>
                    <canvas id="topEmployeesChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Task Analytics Section -->
        <h2 class="section-title"><i class="fas fa-tasks"></i> Task Management Analytics</h2>
        <div class="row mb-5">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $task_analytics['by_status']['completed'] ?? 0; ?></div>
                    <div class="stats-label">Completed Tasks</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $task_analytics['by_status']['pending'] ?? 0; ?></div>
                    <div class="stats-label">Pending Tasks</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $task_analytics['by_status']['in_progress'] ?? 0; ?></div>
                    <div class="stats-label">In Progress</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="border-left-color: #dc3545;">
                    <div class="stats-number" style="color: #dc3545;"><?php echo $task_analytics['overdue']; ?></div>
                    <div class="stats-label">Overdue Tasks</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Tasks by Status</h5>
                    <canvas id="taskStatusChart" width="400" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Tasks by Priority</h5>
                    <canvas id="taskPriorityChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Website Analytics Section -->
        <h2 class="section-title"><i class="fas fa-eye"></i> Website Analytics</h2>
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $post_analytics['total_views']; ?></div>
                    <div class="stats-label">Total Page Views</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $post_analytics['unique_visitors']; ?></div>
                    <div class="stats-label">Unique Visitors</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $post_analytics['total_views'] > 0 ? round(($post_analytics['total_views'] / $post_analytics['unique_visitors']), 2) : 0; ?></div>
                    <div class="stats-label">Pages per Visitor</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Traffic by Device</h5>
                    <canvas id="deviceChart" width="400" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <div class="chart-container">
                    <h5>Traffic by Country</h5>
                    <canvas id="countryChart" width="400" height="200"></canvas>
                </div>
            </div>
            <div class="col-md-12">
                <div class="chart-container">
                    <h5>Popular Pages</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Page</th>
                                    <th>Title</th>
                                    <th>Views</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($post_analytics['popular_pages'] as $page): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($page['page_url']); ?></td>
                                        <td><?php echo htmlspecialchars($page['page_title']); ?></td>
                                        <td><span class="badge bg-primary"><?php echo $page['views']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Visa Status Chart
        const visaStatusCtx = document.getElementById('visaStatusChart').getContext('2d');
        new Chart(visaStatusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($visa_analytics['by_status'])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($visa_analytics['by_status'])); ?>,
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#17a2b8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Destination Chart
        const destCtx = document.getElementById('destinationChart').getContext('2d');
        new Chart(destCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($visa_analytics['by_destination'], 'destination')); ?>,
                datasets: [{
                    label: 'Submissions',
                    data: <?php echo json_encode(array_column($visa_analytics['by_destination'], 'count')); ?>,
                    backgroundColor: '#667eea'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Attendance Status Chart
        const attStatusCtx = document.getElementById('attendanceStatusChart').getContext('2d');
        new Chart(attStatusCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_keys($attendance_analytics['by_status'])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($attendance_analytics['by_status'])); ?>,
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#17a2b8']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Top Employees Chart
        const empCtx = document.getElementById('topEmployeesChart').getContext('2d');
        new Chart(empCtx, {
            type: 'horizontalBar',
            data: {
                labels: <?php echo json_encode(array_column($attendance_analytics['top_employees'], 'employee_name')); ?>,
                datasets: [{
                    label: 'Hours Worked',
                    data: <?php echo json_encode(array_column($attendance_analytics['top_employees'], 'total_hours')); ?>,
                    backgroundColor: '#764ba2'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Task Status Chart
        const taskStatusCtx = document.getElementById('taskStatusChart').getContext('2d');
        new Chart(taskStatusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($task_analytics['by_status'])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($task_analytics['by_status'])); ?>,
                    backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Task Priority Chart
        const taskPriorityCtx = document.getElementById('taskPriorityChart').getContext('2d');
        new Chart(taskPriorityCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($task_analytics['by_priority'])); ?>,
                datasets: [{
                    label: 'Tasks',
                    data: <?php echo json_encode(array_values($task_analytics['by_priority'])); ?>,
                    backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Device Chart
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_keys($post_analytics['by_device'])); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_values($post_analytics['by_device'])); ?>,
                    backgroundColor: ['#667eea', '#764ba2', '#f093fb']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Country Chart
        const countryCtx = document.getElementById('countryChart').getContext('2d');
        new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($post_analytics['by_country'], 'country')); ?>,
                datasets: [{
                    label: 'Visitors',
                    data: <?php echo json_encode(array_column($post_analytics['by_country'], 'count')); ?>,
                    backgroundColor: '#667eea'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    
</body>
</html>
