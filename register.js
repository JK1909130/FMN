document.getElementById("registerForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const username = document.getElementById("username").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const confirm = document.getElementById("confirm").value;
  const error = document.getElementById("error");

  if (password !== confirm) {
    error.textContent = "Passwords do not match";
    return;
  }

  const res = await fetch("api/register.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ username, email, password })
  });

  const data = await res.json();

  if (data.success) {
    window.location.href = "login.html";
  } else {
    error.textContent = data.message;
  }
});
