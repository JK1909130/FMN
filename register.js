const res = await fetch("api/register.php", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify(payload)
});

const text = await res.text();

let data;
try {
  data = JSON.parse(text);
} catch {
  console.error("Invalid JSON:", text);
  alert("Server error");
  return;
}

if (!res.ok) {
  alert(data.error || "Registration failed");
  return;
}
