document.addEventListener("DOMContentLoaded", () => {
    // 1. SELECT ELEMENTS
    const financeForm = document.getElementById("financeForm");
    const spendingsList = document.getElementById("spendingsList");
    const totalDisplay = document.getElementById("totalAmount");
    const totalLabel = document.getElementById("totalLabel");
    const dateInput = document.getElementById("dateInput");
    const categorySelect = document.getElementById("categorySelect");
    const categorySummary = document.getElementById("categorySummary");
    const ctx = document.getElementById('financeChart').getContext('2d');

    // 2. CONFIGURATION
    const todayStr = "2025-12-23"; 
    if (dateInput) dateInput.value = todayStr;

    let transactions = [];
    let myChart;
    let currentView = 'week'; 

    const viewNames = {
        today: "Spent Today",
        week: "Spent This Week",
        month: "Spent This Month",
        year: "Spent This Year"
    };

    // 3. INITIALIZE CHART
    function initChart() {
        myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [{
                    label: 'Spending',
                    data: [],
                    borderColor: '#7b6cff',
                    backgroundColor: 'rgba(123, 108, 255, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 4. LOAD CATEGORIES
    async function loadCategories() {
    try {
        const res = await fetch("api/get_categories.php");
        const data = await res.json();
        
        if (data.success) {
            const categorySelect = document.getElementById("categorySelect");
            // Clear everything and reset the header
            categorySelect.innerHTML = '<option value="">Category</option><option value="new">+ Add New</option>';
            
            // Loop through what came back from the database
            data.items.forEach(cat => {
                const opt = document.createElement("option");
                opt.value = cat.id; // The database ID
                opt.textContent = cat.name; // The name (e.g., Food)
                categorySelect.appendChild(opt);
            });
            console.log("Categories loaded:", data.items);
        }
    } catch (e) {
        console.error("Failed to fetch categories:", e);
    }
}

    // 5. HANDLE NEW CATEGORY PROMPT
    categorySelect.addEventListener("change", async (e) => {
    const selectedValue = e.target.value;

    if (selectedValue === "new") {
        const newName = prompt("Enter new category name:");
        
        // If user hits cancel or leaves it blank, stop here
        if (!newName || newName.trim() === "") {
            categorySelect.value = "";
            return;
        }

        console.log("Attempting to create category:", newName);

        try {
            const res = await fetch("api/add_category.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ name: newName.trim() })
            });

            const result = await res.json();
            
            if (result.success) {
                alert("Category '" + newName + "' created successfully!");
                await loadCategories(); // Refresh the dropdown list
                
                // Automatically select the newly created category
                Array.from(categorySelect.options).forEach((option, index) => {
                    if (option.text === newName.trim()) {
                        categorySelect.selectedIndex = index;
                    }
                });
            } else {
                alert("Server rejected category: " + (result.message || "Unknown error"));
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            alert("Connection error: Could not reach the server.");
        }
    }
});

    // 6. LOAD TRANSACTIONS
    async function loadSpendings() {
        try {
            const res = await fetch("api/get_finance.php");
            const data = await res.json();
            if (data.success) {
                transactions = data.items;
                renderList();
                updateCategoryUI();
                switchView(currentView);
            }
        } catch (e) { console.error("Database Load Error:", e); }
    }

    // 7. CHART & TOTAL LOGIC
    window.switchView = (view) => {
        currentView = view;
        const dataPoints = [];
        const labels = [];
        const now = new Date(todayStr + "T00:00:00");
        let periodTotal = 0;

        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.remove('active');
            if(btn.textContent.toLowerCase().includes(view.replace('today','day'))) btn.classList.add('active');
        });

        totalLabel.textContent = viewNames[view];

        if (view === 'today') {
            labels.push("Morning", "Noon", "Afternoon", "Evening", "Night");
            const items = transactions.filter(t => t.date === todayStr);
            let slots = [0, 0, 0, 0, 0];
            items.forEach((t, i) => slots[i % 5] += parseFloat(t.amount));
            let running = 0;
            slots.forEach(s => { running += s; dataPoints.push(running); });
            periodTotal = items.reduce((s, t) => s + parseFloat(t.amount), 0);
        } 
        else if (view === 'week') {
            for (let i = 6; i >= 0; i--) {
                const d = new Date(now); d.setDate(now.getDate() - i);
                const dStr = d.toLocaleDateString('en-CA');
                labels.push(d.toLocaleDateString('en-US', { weekday: 'short' }));
                const sum = transactions.filter(t => t.date === dStr).reduce((s, t) => s + parseFloat(t.amount), 0);
                dataPoints.push(sum);
                periodTotal += sum;
            }
        }
        else if (view === 'month') {
            labels.push("Week 1", "Week 2", "Week 3", "Week 4");
            const oneMonthAgo = new Date(now); oneMonthAgo.setDate(now.getDate() - 30);
            for (let i = 3; i >= 0; i--) {
                const end = new Date(now); end.setDate(now.getDate() - (i * 7));
                const start = new Date(end); start.setDate(end.getDate() - 7);
                const weekSum = transactions.filter(t => {
                    const d = new Date(t.date + "T00:00:00");
                    return d > start && d <= end;
                }).reduce((s, t) => s + parseFloat(t.amount), 0);
                dataPoints.push(weekSum);
            }
            periodTotal = transactions.filter(t => new Date(t.date + "T00:00:00") >= oneMonthAgo).reduce((s, t) => s + parseFloat(t.amount), 0);
        }
        else if (view === 'year') {
            labels.push("Oct", "Nov", "Dec");
            const oneYearAgo = new Date(now); oneYearAgo.setDate(now.getDate() - 365);
            [9, 10, 11].forEach(mIndex => {
                const sum = transactions.filter(t => new Date(t.date + "T00:00:00").getMonth() === mIndex).reduce((s, t) => s + parseFloat(t.amount), 0);
                dataPoints.push(sum);
            });
            periodTotal = transactions.filter(t => new Date(t.date + "T00:00:00") >= oneYearAgo).reduce((s, t) => s + parseFloat(t.amount), 0);
        }

        totalDisplay.textContent = Math.round(periodTotal).toLocaleString();
        myChart.data.labels = labels;
        myChart.data.datasets[0].data = dataPoints;
        myChart.update();
    };

    // 8. CATEGORY PILLS UI
    function updateCategoryUI() {
        categorySummary.innerHTML = "";
        const cats = {};
        transactions.forEach(t => {
            const name = t.category_name || "General";
            cats[name] = (cats[name] || 0) + parseFloat(t.amount);
        });
        for (const [name, val] of Object.entries(cats)) {
            const span = document.createElement("span");
            span.style = "background:#f0f0f0; padding:5px 12px; border-radius:15px; font-size:12px; font-weight:600;";
            span.innerHTML = `${name}: <span style="color:#7b6cff">NT$ ${val.toLocaleString()}</span>`;
            categorySummary.appendChild(span);
        }
    }

    // 9. RENDER HISTORY LIST
    function renderList() {
        spendingsList.innerHTML = "";
        transactions.forEach(t => {
            const div = document.createElement("div");
            div.className = "spending-item";
            div.style = "display:flex; justify-content:space-between; align-items:center; padding:12px; border-bottom:1px solid #f0f0f0;";
            div.innerHTML = `
                <div>
                    <strong style="display:block;">${t.description}</strong>
                    <small style="color:#999;">${t.date} • ${t.category_name || 'General'}</small>
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <strong>NT$ ${parseFloat(t.amount).toLocaleString()}</strong>
                    <button onclick="deleteItem(${t.id})" style="background:none; border:none; cursor:pointer; font-size:16px;">🗑️</button>
                </div>
            `;
            spendingsList.appendChild(div);
        });
    }

    // 10. GLOBAL DELETE
    window.deleteItem = async function(id) {
        if (!confirm("Delete this entry?")) return;
        try {
            const res = await fetch("api/delete_finance.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id: id })
            });
            if ((await res.json()).success) loadSpendings();
        } catch (e) { console.error("Delete Error:", e); }
    };

    // 11. FORM SUBMIT (ADD ITEM)
    financeForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    
    // 1. Get the values from the inputs
    const desc = document.getElementById("desc").value;
    const amt = document.getElementById("amount").value;
    const date = document.getElementById("dateInput").value;
    const catSelect = document.getElementById("categorySelect");
    
    // 2. Capture the Category ID
    // If the user hasn't selected anything, or it's empty, we want null
    let categoryId = catSelect.value;
    if (categoryId === "" || categoryId === "new") {
        categoryId = null;
    }

    const payload = {
        description: desc,
        amount: amt,
        category_id: categoryId, 
        date: date
    };

    // DEBUG: Open your browser console (F12) to see this!
    console.log("Sending to database:", payload);

    try {
        const res = await fetch("api/add_finance.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        });

        const result = await res.json();
        if (result.success) {
            financeForm.reset();
            dateInput.value = todayStr; // Reset date to Dec 23
            await loadSpendings(); // This refreshes the chart and list
        }
    } catch (err) {
        console.error("Transmission error:", err);
    }
});

    // 12. BOOTSTRAP
    initChart();
    loadCategories();
    loadSpendings();
});
