console.log("music.js loaded");

document.addEventListener("DOMContentLoaded", () => {

  async function loadMusic() {
    const res = await fetch("api/music_list.php");
    const tracks = await res.json();

    const ul = document.getElementById("musicList");
    if (!ul) return;

    ul.innerHTML = "";

    tracks.forEach(t => {
      const li = document.createElement("li");

      const name = document.createElement("strong");
      name.textContent = t.title || t.youtube_id;

      const del = document.createElement("button");
      del.textContent = "❌";
      del.onclick = () => removeSong(t.id);

      li.appendChild(name);
      li.appendChild(del);
      ul.appendChild(li);
    });
  }

  async function loadMusicPoints() {
    const res = await fetch("api/me.php");
    const me = await res.json();

    const el = document.getElementById("musicPoints");
    if (!el) return;

    el.textContent = `Music points: ${me.music_points}`;
  }

  window.addSong = async function () {
    const input = document.getElementById("ytLink");
    if (!input) return;

    const link = input.value.trim();
    if (!link) return;

    const title = prompt("Name this song:");
    if (!title) return;

    const res = await fetch("api/music_add.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ link, title })
    });

    if (!res.ok) {
      alert("Not enough music points");
      return;
    }

    input.value = "";
    await loadMusicPoints();
    await loadMusic();
  };

  async function removeSong(id) {
    await fetch("api/music_delete.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ id })
    });

    await loadMusicPoints();
    await loadMusic();
  }

  // initial load
  loadMusicPoints();
  loadMusic();
});
