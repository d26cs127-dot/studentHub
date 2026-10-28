<?php

include 'db.php';


if (isset($_POST['add_student'])) {

    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);

    $insert_sql = "INSERT INTO students (name, email, course, gender, status)
                   VALUES ('$name', '$email', '$course', '$gender', 'active')";

    if (mysqli_query($conn, $insert_sql)) {
        header("Location: index.php?message=added");
    } else {
        header("Location: index.php?message=error");
    }
    exit();
}

if (isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    $delete_sql = "DELETE FROM students WHERE id = $id";

    if (mysqli_query($conn, $delete_sql)) {
        header("Location: index.php?message=permanently_deleted");
    } else {
        header("Location: index.php?message=error");
    }
    exit();
}

if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    $status_result = mysqli_query($conn, "SELECT status FROM students WHERE id = $id LIMIT 1");
    $status_row = $status_result ? mysqli_fetch_assoc($status_result) : null;
    $new_status = ($status_row && $status_row['status'] === 'inactive') ? 'active' : 'inactive';
    $toggle_sql = "UPDATE students SET status = '$new_status' WHERE id = $id";

    if (mysqli_query($conn, $toggle_sql)) {
        header("Location: index.php?message=status_changed");
    } else {
        header("Location: index.php?message=error");
    }
    exit();
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $export_result = mysqli_query($conn, 'SELECT id, name, email, course, gender, status, created_at FROM students ORDER BY id DESC');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="students.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Name', 'Email', 'Course', 'Gender', 'Status', 'Created At']);
    while ($row = mysqli_fetch_assoc($export_result)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}


$message = $_GET['message'] ?? '';


/* ================================
   SEARCH AND FILTER
================================ */

$search = isset($_GET['search'])
    ? mysqli_real_escape_string($conn, $_GET['search'])
    : '';

$course_filter = isset($_GET['course_filter'])
    ? mysqli_real_escape_string($conn, $_GET['course_filter'])
    : '';

$conditions = [];


/* Search by name or email */

if (!empty($search)) {

    $conditions[] =
        "(name LIKE '%$search%' OR email LIKE '%$search%')";
}


/* Filter by course */

if (!empty($course_filter)) {

    $conditions[] =
        "course = '$course_filter'";
}


/* Build SELECT query */

$query_sql = "SELECT * FROM students";

if (count($conditions) > 0) {

    $query_sql .= " WHERE " . implode(" AND ", $conditions);
}

$query_sql .= " ORDER BY id DESC";

$result = mysqli_query($conn, $query_sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Student Management System</title>
<link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">

    <h1>Student Management System</h1>

    <?php if ($message === 'added'): ?>
        <p class="notice success" role="status">Student added successfully.</p>
    <?php elseif ($message === 'updated'): ?>
        <p class="notice success" role="status">Student details updated successfully.</p>
    <?php elseif ($message === 'status_changed'): ?>
        <p class="notice success" role="status">Student status changed successfully.</p>
    <?php elseif ($message === 'permanently_deleted'): ?>
        <p class="notice success" role="status">Student record permanently deleted.</p>
    <?php elseif ($message === 'error'): ?>
        <p class="notice error" role="alert">The requested operation could not be completed.</p>
    <?php endif; ?>


    <!-- ================================
         ADD STUDENT
    ================================= -->

    <div class="card">

        <h2>Add New Student</h2>

        <form method="POST" action="index.php">

            <div class="form-group">

                <input
                    type="text"
                    name="name"
                    placeholder="Full Name"
                    required
                >

                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    required
                >

                <select name="course" required>

                    <option value="">
                        Select Course
                    </option>

                    <option value="Computer Science">
                        Computer Science
                    </option>

                    <option value="Web Development">
                        Web Development
                    </option>

                    <option value="Data Science">
                        Data Science
                    </option>

                </select>

                <select name="gender" required>

                    <option value="">
                        Select Gender
                    </option>

                    <option value="Male">
                        Male
                    </option>

                    <option value="Female">
                        Female
                    </option>

                </select>

            </div>

            <button
                type="submit"
                name="add_student"
                class="add-btn"
            >
                Add Student
            </button>

        </form>

    </div>


    <!-- ================================
         SEARCH AND FILTER
    ================================= -->

    <div class="card">

        <h2>Search & Filter Students</h2>

        <form
            method="GET"
            action="index.php"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                placeholder="Search by name or email"
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <select name="course_filter">

                <option value="">
                    All Courses
                </option>

                <option
                    value="Computer Science"
                    <?php
                    if ($course_filter == "Computer Science")
                        echo "selected";
                    ?>
                >
                    Computer Science
                </option>

                <option
                    value="Web Development"
                    <?php
                    if ($course_filter == "Web Development")
                        echo "selected";
                    ?>
                >
                    Web Development
                </option>

                <option
                    value="Data Science"
                    <?php
                    if ($course_filter == "Data Science")
                        echo "selected";
                    ?>
                >
                    Data Science
                </option>

            </select>

            <button
                type="submit"
                class="filter-btn"
            >
                Filter
            </button>

            <a
                href="index.php"
                class="reset-btn"
            >
                Reset
            </a>

            <a href="index.php?export=csv" class="export-btn">Export CSV</a>

        </form>


        <!-- ================================
             STUDENT TABLE
        ================================= -->

        <table>

            <thead>

                <tr>

                    <th>ID</th>

                    <th>Name</th>

                    <th>Email</th>

                    <th>Course</th>

                    <th>Gender</th>

                    <th>Status</th>

                    <th>Actions</th>

                </tr>

            </thead>

            <tbody>

            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($result)): ?>

                    <tr>

                        <td>
                            <?php echo $row['id']; ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars($row['name']);
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars($row['email']);
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars($row['course']);
                            ?>
                        </td>

                        <td>
                            <?php
                            echo htmlspecialchars($row['gender']);
                            ?>
                        </td>

                        <td>
                            <span class="status-badge <?php echo $row['status'] === 'inactive' ? 'inactive' : 'active'; ?>">
                                <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                            </span>
                        </td>

                        <td>

                            <a
                                href="edit.php?id=<?php echo $row['id']; ?>"
                                class="edit-btn"
                            >
                                Edit
                            </a>

                            |

                            <a
                                href="index.php?toggle_status=<?php echo $row['id']; ?>"
                                class="status-btn"
                                onclick="return confirm('Change this student status?');"
                            >
                                Change status
                            </a>

                            |

                            <a
                                href="index.php?delete=<?php echo $row['id']; ?>"
                                class="delete-btn"
                                onclick="return confirm('Permanently delete this record? This cannot be undone.');"
                            >
                                Delete permanently
                            </a>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="7"
                        class="no-records"
                    >
                        No records found.
                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</body>

</html>

