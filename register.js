document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("registerForm");
  const errorEl = document.getElementById("error");

  form.addEventListener("submit", async e => {
    e.preventDefault();
    errorEl.textContent = "";

    const username = document.getElementById("username").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirm").value;

    if (!username || !email || !password || !confirm) {
      errorEl.textContent = "All fields are required";
      return;
    }

    if (password !== confirm) {
      errorEl.textContent = "Passwords do not match";
      return;
    }

    try {
      const res = await fetch("/api/register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username, email, password })
      });

      const data = await res.json();

      if (!res.ok) {
        errorEl.textContent = data.error || "Register failed";
        return;
      }

      // success → go login
      location.href = "login.html";

    } catch (err) {
      errorEl.textContent = "Server error";
    }
  });
});
