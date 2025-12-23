<?php
session_start();
if (!isset($_SESSION["user_id"])) {
  header("Location: login.html");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Forget-Me-Note</title>
  <link rel="stylesheet" href="tasks.css">
</head>
<body>

<header class="topbar">
  <button onclick="location.href='index.html'">←</button>
  <h1>Tasks</h1>
</header>

<main class="content">

  <div class="add-task">
    <input id="title" placeholder="Task title">
    <select id="category">
      <option>Homework</option>
      <option>Work</option>
      <option>Home</option>
    </select>
    <input type="datetime-local" id="deadline">
    <button onclick="addTask()">+</button>
  </div>

  <ul id="taskList"></ul>

</main>

<!-- ✅ expose current user id BEFORE tasks.js -->
<script>
  window.CURRENT_USER_ID = <?= (int)$_SESSION["user_id"] ?>;
</script>

<script src="tasks.js"></script>
</body>
</html>
