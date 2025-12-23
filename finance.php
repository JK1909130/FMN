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
  <meta charset="UTF-8" />
  <title>Spendings - Forget-Me-Note</title>
  
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <script>
    fetch("api/auth.php")
      .then(res => { if (!res.ok) window.location.href = "login.html"; })
      .catch(() => {}); // catch error if file missing
  </script>
  <link rel="stylesheet" href="finance.css" />
</head>
<body>

<div class="app">
  <header class="topbar">
    <button onclick="location.href='index.html'" class="back-btn">←</button>
    <h1>Spendings</h1>
    <div style="width: 40px;"></div>
  </header>

  <main class="finance-container">
    
      <div class="total-card">
          <span id="totalLabel">Spent This Week</span>
          <div class="amount-row">
              <span class="currency">NT$</span>
              <span id="totalAmount" class="amount">0</span>
          </div>
      </div>

      <div class="chart-card" style="height: 350px; display: flex; flex-direction: column;">
          <div class="chart-controls" style="display: flex; gap: 10px; margin-bottom: 15px;">
              <button class="view-btn" onclick="switchView('today')">Day</button>
              <button class="view-btn active" onclick="switchView('week')">Week</button>
              <button class="view-btn" onclick="switchView('month')">Month</button>
              <button class="view-btn" onclick="switchView('year')">Year</button>
          </div>
          <div style="flex: 1; width: 100%; min-height: 0;">
              <canvas id="financeChart"></canvas>
          </div>
      </div>

      <div class="category-card" style="margin-top: 20px; padding: 15px; background: #fff; border-radius: 12px; border: 1px solid #eee;">
          <h4 style="margin-bottom: 10px;">Spending by Category</h4>
          <div id="categorySummary" style="display: flex; flex-wrap: wrap; gap: 8px;">
              </div>
      </div>

      <div class="add-spending-box" style="margin-top: 20px;">
          <form id="financeForm" style="display: flex; gap: 10px; flex-wrap: wrap;">
              <input type="text" id="desc" placeholder="What did you buy?" required />
              <input type="number" id="amount" placeholder="NT$" required />
              
              <select id="categorySelect">
                  <option value="">Category</option>
                  <option value="new">+ Add New</option>
              </select>

              <input type="date" id="dateInput" required />
              <button type="submit" class="add-btn">+</button>
          </form>
      </div>

      <div class="spendings-container" style="margin-top: 20px;">
          <h3 style="margin-bottom: 15px;">Recent History</h3>
          <div id="spendingsList"></div>
      </div>

  </main>
</div>

<script src="finance.js"></script>
</body>
</html>

