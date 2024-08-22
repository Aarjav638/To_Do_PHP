<?php
// Connection with the database
$conn = new mysqli('sql110.infinityfree.com', 'if0_37159030', 'ZgfSv3qnzz4TE7j', 'if0_37159030_to_do');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task'])) {
    $task = $conn->real_escape_string($_POST['task']);
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        $conn->query("UPDATE tasks SET task='$task' WHERE id=$id");
    } else {
        $conn->query("INSERT INTO tasks (task) VALUES ('$task')");
    }

    header('Location: index.php');
    exit();
}

// Handle task deletion
if (isset($_GET['del_task'])) {
    $id = $_GET['del_task'];
    
    // Delete the task
    $conn->query("DELETE FROM tasks WHERE id=$id");

    $result = $conn->query("SELECT * FROM tasks");

    if ($result->num_rows > 0) {
        $counter = 1;
        while ($row = $result->fetch_assoc()) {
            $currentId = $row['id'];
            $conn->query("UPDATE tasks SET id=$counter WHERE id=$currentId");
            $counter++;
        }

        $conn->query("ALTER TABLE tasks AUTO_INCREMENT = $counter");
    } else {
        $conn->query("ALTER TABLE tasks AUTO_INCREMENT = 1");
    }
    header('Location: index.php');
    exit();
}

// Handle task update
if (isset($_GET['edit_task'])) {
    $id = $_GET['edit_task'];
    $edit_state = true;
    $edit_task = $conn->query("SELECT * FROM tasks WHERE id=$id")->fetch_assoc();
}

// Fetch all tasks
$result = $conn->query("SELECT * FROM tasks ORDER BY id ASC");

$searchQuery = '';
if (isset($_GET['search'])) {
    $searchQuery = $conn->real_escape_string($_GET['search']);
    $result = $conn->query("SELECT * FROM tasks WHERE task LIKE '%$searchQuery%' ORDER BY id ASC");
} else {
    $result = $conn->query("SELECT * FROM tasks ORDER BY id ASC");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    background-color: #161622;
    color: #ffffff;
    display: flex;
    flex-direction: column;
    height: 100vh;
    padding: 20px;
    overflow:auto;
}
h2 {
    color: #FF8E01;
    text-align: center;
}
table {
    width: 50%;
    margin: 30px 0;
    border-collapse: collapse;
    background-color: #1e1e2d;
    border-radius: 8px;
    overflow: hidden;
    align-self: center;
}
th, td {
    border: 1px solid #FF8E01;
    padding: 15px;
    text-align: left;
}
th {
    background-color: #FF8E01;
    color: #161622;
}
td a {
    color: #FF8E01;
    text-decoration: none;
    padding: 5px 10px;
    border-radius: 4px;
    background-color: #1e1e2d;
    border: 1px solid #FF8E01;
}
td a:hover {
    background-color: #FF8E01;
    color: #161622;
}
#addTask {
    text-align: center;
    margin-top: 20px;
    background-color: #1e1e2d;
    padding: 20px;
    border-radius: 8px;
    display: flex;
    justify-content: center;
    align-items: center;
    align-self: center;
}
input[type="text"] {
    width: 70%;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #FF8E01;
    background-color: #1e1e2d;
    color: #ffffff;
}
input[type="submit"] {
    padding: 10px 20px;
    border-radius: 4px;
    margin-left: 10px;
    background-color: #FF8E01;
    border: none;
    color: #161622;
    cursor: pointer;
}
input[type="submit"]:hover {
    background-color: #ffffff;
    color: #FF8E01;
}
.message {
    margin-top: 20px;
    background-color: #1e1e2d;
    padding: 20px;
    border-radius: 8px;
    color: #FF8E01;
    text-align: center;
    align-self: center;
}
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    width: 100%;

}

#id{
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #FF8E01;
    background-color: #1e1e2d;
    color: #ffffff;
    max-width: 50px;
}
#search{
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #FF8E01;
    background-color: #1e1e2d;
    color: #ffffff;
    width: 60%;
}
.search{
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    width: 30%;
}

@media screen and (max-width: 650px){
    table{
        width: 100%;
    }
    input[type="text"]{
        width: 100%;
    }
    input[type="submit"]{
        width: 100%;
        margin-left: 0;
    }
    #id{
        width: 30%;
    }
    
    #addTask{
        flex-direction: column;
        display: flex;
        gap: 10px;
        width: 80%;
        margin-bottom:4%

    }
    header{
        flex-direction: column;
        display: flex;
        padding: 4px;
        justify-content: center;
        align-items: center;
    }
    h1{
        font-size: 1.5rem;
    }
    #button_container{
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    #search{
        width: 100%;
        margin: 10px 0px;
    }
    .search{
        width: 80%;
        flex-direction: column;
    }
    
}
    </style>
</head>
<body >
<header>
    <h1>Simple To-Do List</h1>
    <form method="GET" action="index.php" class="search">
        <input id="search" name="search" placeholder="Search tasks..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        <input type="submit" value="Search">
    </form>

</header>

<h2>To-Do List</h2>

<?php if ($result->num_rows > 0) { ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Task</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo $row['task']; ?></td>
                <td id="button_container">
                    <a  href="index.php?edit_task=<?php echo $row['id']; ?>">Edit</a>
                    <a href="index.php?del_task=<?php echo $row['id']; ?>">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
<?php } else { ?>
    <div class="message">No tasks available. Please add a task to Continue</div>
<?php } ?>

<form method="POST" action="index.php" id="addTask">
    <input type="text" name="task" placeholder="Enter a new task" value="<?php echo isset($edit_task) ? $edit_task['task'] : ''; ?>" required>
    <?php if (isset($edit_task)) { ?>
        <input type="hidden" name="id" value="<?php echo $edit_task['id']; ?>">
        <input type="submit" value="Update Task">
    <?php } else { ?>
        <input type="submit" value="Add Task">
    <?php } ?>
</form>

</body>
</html>
