document.getElementById("registerForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const username = document.getElementById("username").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const confirm = document.getElementById("confirm").value;

  if (!username || !email || !password || !confirm) {
    document.getElementById("error").textContent = "All fields required";
    return;
  }

  if (password !== confirm) {
    document.getElementById("error").textContent = "Passwords do not match";
    return;
  }

  const res = await fetch("api/register.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      username,
      email,
      password
    })
  });

  const data = await res.json();

  if (!res.ok) {
    document.getElementById("error").textContent = data.error || "Register failed";
    return;
  }

  location.href = "login.html";
});
