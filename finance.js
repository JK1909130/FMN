async function loadExpenses() {
  const res = await fetch("api/expenses.php");
  const expenses = await res.json();

  const list = document.getElementById("expenseList");
  const totalEl = document.getElementById("total");
  const bars = document.getElementById("categoryBars");

  list.innerHTML = "";
  bars.innerHTML = "";

  let total = 0;
  const categories = {};

  expenses.forEach(e => {
    total += parseFloat(e.amount);
    categories[e.category] =
      (categories[e.category] || 0) + parseFloat(e.amount);

    const li = document.createElement("li");
    li.className = "expense";
    li.innerHTML = `
      <div>
        <strong>${e.name}</strong>
        <small>${e.category} • ${e.expense_date}</small>
      </div>
      <div>
        $${e.amount}
        <button onclick="deleteExpense(${e.id})">❌</button>
      </div>
    `;
    list.appendChild(li);
  });

  totalEl.textContent = `Total: $${total.toFixed(2)}`;

  Object.entries(categories).forEach(([cat, amt]) => {
    const percent = total ? (amt / total) * 100 : 0;

    const div = document.createElement("div");
    div.className = "bar";
    div.innerHTML = `
      <div class="bar-label">${cat} ($${amt.toFixed(2)})</div>
      <div class="bar-track">
        <div class="bar-fill" style="width:${percent}%"></div>
      </div>
    `;
    bars.appendChild(div);
  });
}

async function addExpense() {
  const name = document.getElementById("name").value.trim();
  const amount = document.getElementById("amount").value;
  const category = document.getElementById("category").value;
  const date = document.getElementById("date").value;

  if (!name || !amount || !date) return;

  await fetch("api/expenses.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ name, amount, category, date })
  });

  document.getElementById("name").value = "";
  document.getElementById("amount").value = "";
  loadExpenses();
}

async function deleteExpense(id) {
  await fetch("api/expenses.php", {
    method: "DELETE",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id })
  });
  loadExpenses();
}

loadExpenses();
