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
  <title>Forget-Me-Note — Finance</title>
  <link rel="stylesheet" href="finance.css">
</head>
<body>

<header class="topbar">
  <button onclick="location.href='index.html'">←</button>
  <h1>Finance</h1>
</header>

<main class="content">

  <!-- ADD EXPENSE -->
  <div class="add-expense">
    <input id="name" placeholder="Expense name">
    <input id="amount" type="number" step="0.01" placeholder="Amount">
    <select id="category">
      <option>Food</option>
      <option>Transport</option>
      <option>Entertainment</option>
      <option>Bills</option>
      <option>Other</option>
    </select>
    <input id="date" type="date">
    <button onclick="addExpense()">+</button>
  </div>

  <!-- SUMMARY -->
  <section class="summary">
    <h3>This Month</h3>
    <div id="total"></div>
    <div id="categoryBars"></div>
  </section>

  <!-- EXPENSE LIST -->
  <ul id="expenseList"></ul>

</main>

<script src="finance.js"></script>
</body>
</html>
