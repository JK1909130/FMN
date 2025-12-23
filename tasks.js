let openChecklistTaskId = null;

/* ===============================
   URGENCY LOGIC
   =============================== */
function urgencyClass(task) {
  if (!task.deadline) return "safe";

  const created = new Date(task.created_at).getTime();
  const deadline = new Date(task.deadline).getTime();
  const now = Date.now();

  const total = deadline - created;
  const timeLeft = deadline - now;

  if (total <= 0) return "urgent";
  if (timeLeft <= 0) return "overdue";

  const percentLeft = timeLeft / total;
  if (percentLeft <= 0.3) return "urgent";

  return "safe";
}

/* ===============================
   LOAD & RENDER TASKS
   =============================== */
async function loadTasks() {
  const res = await fetch("api/tasks.php");
  const tasks = await res.json();
  const list = document.getElementById("taskList");

  list.innerHTML = "";

  // sort by urgency
  tasks.sort((a, b) => {
    const score = t => {
      if (!t.deadline) return 0;
      const total = new Date(t.deadline) - new Date(t.created_at);
      const left = new Date(t.deadline) - Date.now();
      if (left <= 0) return 3;
      if (left / total <= 0.3) return 2;
      return 1;
    };
    return score(b) - score(a);
  });

  tasks.forEach(t => {
    const isOwner = Number(t.owner_id) === Number(window.CURRENT_USER_ID);

    const li = document.createElement("li");
    li.className = `
      task
      ${urgencyClass(t)}
      ${t.completed ? "done" : ""}
      ${!isOwner ? "shared-task" : ""}
    `;

    li.innerHTML = `
      <div class="task-header" onclick="toggleChecklist(${t.id})">
        <strong>${t.title}</strong>
        <small>${t.category}</small>
        ${t.deadline ? `<small>⏰ ${new Date(t.deadline).toLocaleString()}</small>` : ""}
      </div>

        <div class="task-collaborators" id="collab-${t.id}"></div>

      <div class="task-body hidden" id="body-${t.id}">
        <div class="checklist" id="cl-${t.id}"></div>
        <input class="cl-input"
               placeholder="Add item..."
               onkeydown="addChecklistItem(event, ${t.id})">
      </div>

      <div class="task-actions">
        <button onclick="toggleTask(${t.id}, ${t.completed})">✔</button>
        ${isOwner ? `<button onclick="deleteTask(${t.id})">❌</button>` : ""}
        ${isOwner ? `<button onclick="shareTask(${t.id})">👤+</button>` : ""}
        ${isOwner ? `<button onclick="sendReminder(${t.id})">Remind collaborators</button>` : ""}
      </div>
    `;

    list.appendChild(li);
  });

  // reopen checklist if needed
  if (openChecklistTaskId !== null) {
    setTimeout(() => toggleChecklist(openChecklistTaskId), 0);
  }
}

/* ===============================
   TASK CRUD
   =============================== */
async function addTask() {
  const title = document.getElementById("title").value.trim();
  const category = document.getElementById("category").value;
  const deadline = document.getElementById("deadline").value;

  if (!title) return;

  await fetch("api/tasks.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ title, category, deadline })
  });

  document.getElementById("title").value = "";
  document.getElementById("deadline").value = "";
  loadTasks();
}

async function toggleTask(id, done) {
  await fetch("api/tasks.php", {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id, completed: done ? 0 : 1 })
  });
  loadTasks();
}

async function deleteTask(id) {
  if (!confirm("Delete this task?")) return;

  await fetch("api/tasks.php", {
    method: "DELETE",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id })
  });
  loadTasks();
}
async function sendReminder(taskId) {
  if (!confirm("Send reminder to all collaborators?")) return;

  const res = await fetch("api/send_reminder.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ task_id: taskId })
  });

  if (res.ok) {
    alert("Reminder sent!");
  } else {
    alert("Failed to send reminder");
  }
  loadTasks();
}
/* ===============================
   CHECKLIST LOGIC
   =============================== */
async function toggleChecklist(taskId) {
  const body = document.getElementById(`body-${taskId}`);
  const cl = document.getElementById(`cl-${taskId}`);
  const collab = document.getElementById(`collab-${taskId}`);

  const isOpening = body.classList.contains("hidden");

  // close all open task bodies
  document.querySelectorAll(".task-body").forEach(b => {
    b.classList.add("hidden");
    // remove old activity logs if any
    const log = b.querySelector(".activity-log");
    if (log) log.remove();
  });

  if (!isOpening) {
    openChecklistTaskId = null;
    return;
  }

  openChecklistTaskId = taskId;
  body.classList.remove("hidden");

  /* ===============================
     LOAD CHECKLIST ITEMS
     =============================== */
  const itemsRes = await fetch(`api/task_items.php?task_id=${taskId}`);
  const items = await itemsRes.json();

  cl.innerHTML = "";
  items.forEach(i => renderChecklistItem(cl, i));

  /* ===============================
     LOAD COLLABORATORS
     =============================== */
  const collabRes = await fetch(`api/task_users.php?task_id=${taskId}`);
  const users = await collabRes.json();

  // find owner id from task list (already loaded)
  const task = [...document.querySelectorAll(".task")]
    .map(el => el)
    .find(el => el.querySelector(`[onclick="toggleChecklist(${taskId})"]`));

  // safer: pass owner id via dataset if you want later
  const ownerId = users.find(u => u.owner_id)?.owner_id ?? null;

  const isOwner = users.some(
    u => u.id === window.CURRENT_USER_ID && u.id === ownerId
  );

  collab.innerHTML = `
    <small>
      👥 ${users.map(u => {
        const crown = u.id === ownerId ? "👑 " : "";
        const removeBtn =
          isOwner && u.id !== ownerId
            ? ` <button onclick="removeUser(${taskId}, ${u.id})">❌</button>`
            : "";
        return `${crown}${u.username}${removeBtn}`;
      }).join(", ")}
    </small>
  `;

  /* ===============================
     LOAD ACTIVITY LOG
     =============================== */
  const activityRes = await fetch(`api/task_activity.php?task_id=${taskId}`);
  const activity = await activityRes.json();

  if (activity.length) {
    const logHtml = activity.map(a =>
      `<div>• ${a.username} ${a.action}</div>`
    ).join("");

    body.insertAdjacentHTML(
      "beforeend",
      `<div class="activity-log"><small>${logHtml}</small></div>`
    );
  }
}




function renderChecklistItem(container, item) {
  const div = document.createElement("div");
  div.className = "checklist-item";

  div.innerHTML = `
    <label>
      <input type="checkbox"
             ${item.done ? "checked" : ""}
             onchange="toggleItem(${item.id}, this.checked)">
      ${item.content}
    </label>
    <button class="cl-delete"
            onclick="deleteChecklistItem(${item.id})">❌</button>
  `;

  container.appendChild(div);
}

async function addChecklistItem(e, taskId) {
  if (e.key !== "Enter") return;

  const content = e.target.value.trim();
  if (!content) return;

  await fetch("api/task_items.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ task_id: taskId, content })
  });

  e.target.value = "";
  loadTasks();
}

async function toggleItem(id, done) {
  await fetch("api/task_items.php", {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id, done: done ? 1 : 0 })
  });
}

async function deleteChecklistItem(id) {
  await fetch("api/task_items.php", {
    method: "DELETE",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ id })
  });
  loadTasks();
}

/* ===============================
   SHARE TASK
   =============================== */
async function shareTask(taskId) {
  const email = prompt("Enter user's email to share with:");
  if (!email || !email.includes("@")) return;

  const res = await fetch("api/share_task.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ task_id: taskId, email })
  });

  if (res.ok) {
    alert("Task shared!");
  } else if (res.status === 404) {
    alert("User not found");
  } else if (res.status === 403) {
    alert("Only the owner can share this task");
  } else {
    alert("Failed to share task");
  }
}
async function removeUser(taskId, userId) {
  if (!confirm("Remove this user from task?")) return;

  await fetch("api/remove_task_user.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ task_id: taskId, user_id: userId })
  });

  loadTasks();
}

/* ===============================
   INIT
   =============================== */
loadTasks();
setInterval(loadTasks, 60_000);
