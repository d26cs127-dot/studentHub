<?php

include 'db.php';


/* ================================
   GET STUDENT ID
================================ */

$id = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;


/* ================================
   FETCH STUDENT
================================ */

$select_sql = "SELECT * FROM students WHERE id = $id";

$fetch_result = mysqli_query($conn, $select_sql);

$student = mysqli_fetch_assoc($fetch_result);


if (!$student) {

    die("Student record not found.");

}

$update_error = '';


/* ================================
   UPDATE STUDENT
================================ */

if (isset($_POST['update_student'])) {

    $name =
        mysqli_real_escape_string(
            $conn,
            $_POST['name']
        );

    $email =
        mysqli_real_escape_string(
            $conn,
            $_POST['email']
        );

    $course =
        mysqli_real_escape_string(
            $conn,
            $_POST['course']
        );

    $gender =
        mysqli_real_escape_string(
            $conn,
            $_POST['gender']
        );


    mysqli_query($conn, "SET SESSION innodb_lock_wait_timeout = 5");
    $update_statement = mysqli_prepare(
        $conn,
        'UPDATE students SET name = ?, email = ?, course = ?, gender = ? WHERE id = ?'
    );

    if (!$update_statement) {
        $update_error = 'The update statement could not be prepared.';
    } else {
        mysqli_stmt_bind_param($update_statement, 'ssssi', $name, $email, $course, $gender, $id);
        if (mysqli_stmt_execute($update_statement)) {
            mysqli_stmt_close($update_statement);
            header("Location: index.php?updated=1");
            exit();
        }

        $update_error = mysqli_errno($conn) === 1205
            ? 'This student record is locked by another database session. Commit or close the other edit, then try again.'
            : 'The student could not be updated: ' . mysqli_error($conn);
        mysqli_stmt_close($update_statement);
    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Student</title>
<link rel="stylesheet" href="style.css">

</head>

    
<body>

<div class="container edit-page">

    <h2>Edit Student Details</h2>

    <?php if ($update_error): ?>
        <p class="update-error" role="alert">
            <?php echo htmlspecialchars($update_error, ENT_QUOTES, 'UTF-8'); ?>
        </p>
    <?php endif; ?>


    <form
        method="POST"
        action="edit.php?id=<?php echo $id; ?>"
    >


        <!-- NAME -->

        <div class="form-group">

            <label>Name:</label>

            <input
                type="text"
                name="name"
                value="<?php
                echo htmlspecialchars($student['name']);
                ?>"
                required
            >

        </div>


        <!-- EMAIL -->

        <div class="form-group">

            <label>Email:</label>

            <input
                type="email"
                name="email"
                value="<?php
                echo htmlspecialchars($student['email']);
                ?>"
                required
            >

        </div>


        <!-- COURSE -->

        <div class="form-group">

            <label>Course:</label>

            <select
                name="course"
                required
            >

                <option
                    value="Computer Science"
                    <?php
                    if ($student['course'] == 'Computer Science')
                        echo 'selected';
                    ?>
                >
                    Computer Science
                </option>

                <option
                    value="Web Development"
                    <?php
                    if ($student['course'] == 'Web Development')
                        echo 'selected';
                    ?>
                >
                    Web Development
                </option>

                <option
                    value="Data Science"
                    <?php
                    if ($student['course'] == 'Data Science')
                        echo 'selected';
                    ?>
                >
                    Data Science
                </option>

            </select>

        </div>


        <!-- GENDER -->

        <div class="form-group">

            <label>Gender:</label>

            <select
                name="gender"
                required
            >

                <option
                    value="Male"
                    <?php
                    if ($student['gender'] == 'Male')
                        echo 'selected';
                    ?>
                >
                    Male
                </option>

                <option
                    value="Female"
                    <?php
                    if ($student['gender'] == 'Female')
                        echo 'selected';
                    ?>
                >
                    Female
                </option>

            </select>

        </div>


        <button
            type="submit"
            name="update_student"
        >
            Update
        </button>


        <a
            href="index.php"
            class="cancel"
        >
            Cancel
        </a>


    </form>

</div>

</body>

</html>

