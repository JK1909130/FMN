document.getElementById("registerForm").addEventListener("submit", async (e) => {
  e.preventDefault(); // 🔑 THIS LINE IS REQUIRED

  const username = document.getElementById("username").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;

  const res = await fetch("api/register.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ username, email, password })
  });

  const data = await res.json();
  console.log(data);
});
