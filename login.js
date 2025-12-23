document.getElementById("loginForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value;

  if (!username || !password) {
    document.getElementById("error").textContent = "Missing fields";
    return;
  }

  const res = await fetch("api/login.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      username,
      password
    })
  });

  const data = await res.json();

  if (!res.ok) {
    document.getElementById("error").textContent = data.error || "Login failed";
    return;
  }

  location.href = "index.php";
});
