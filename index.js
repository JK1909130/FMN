/* ===============================
   UI SOUND SYSTEM (FINAL)
   =============================== */

const SOUND_TARGETS = "button, .main-card";

const hoverSound = new Audio("assets/hover.mp3");
const clickSound = new Audio("assets/click.mp3");

hoverSound.volume = 0.4;
clickSound.volume = 0.6;

let audioUnlocked = false;
let lastHover = null;

// unlock audio on first interaction
function unlockAudio() {
  if (audioUnlocked) return;

  clickSound.play().then(() => {
    clickSound.pause();
    clickSound.currentTime = 0;
    audioUnlocked = true;
  }).catch(() => {});

  document.removeEventListener("click", unlockAudio);
  document.removeEventListener("keydown", unlockAudio);
}

document.addEventListener("click", unlockAudio);
document.addEventListener("keydown", unlockAudio);

// hover sound
document.addEventListener("mousemove", e => {
  const el = e.target.closest(SOUND_TARGETS);
  if (!el || el === lastHover || !audioUnlocked) return;

  lastHover = el;

  const sound = el.dataset.hoverSound || "hover.mp3";
  hoverSound.src = `assets/${sound}`;
  hoverSound.currentTime = 0;
  hoverSound.play().catch(() => {});
});

// reset hover tracking
document.addEventListener("mouseout", e => {
  if (!e.relatedTarget?.closest(SOUND_TARGETS)) {
    lastHover = null;
  }
});

// click sound
document.addEventListener("click", e => {
  const el = e.target.closest(SOUND_TARGETS);
  if (!el || !audioUnlocked) return;

  const sound = el.dataset.clickSound || "click.mp3";
  clickSound.src = `assets/${sound}`;
  clickSound.currentTime = 0;
  clickSound.play().catch(() => {});
});



/* ===============================
   GLOBAL STATE
   =============================== */
let playlist = [];
let player = null;
let ytReady = false;

let currentIndex = 0;
let history = [];

/* ===============================
   YOUTUBE API READY
   =============================== */
window.onYouTubeIframeAPIReady = () => {
  ytReady = true;
};

/* ===============================
   LOAD PLAYLIST
   =============================== */
async function loadPlaylist() {
  const res = await fetch("api/music_list.php");
  playlist = await res.json();

  shuffle(playlist);
  currentIndex = 0;
  history = [];
}

/* ===============================
   CREATE PLAYER (ON USER GESTURE)
   =============================== */
async function ensurePlayer() {
  if (player || !ytReady) return;

  player = new YT.Player("player", {
    height: "0",
    width: "0",
    events: {
      onStateChange: e => {
        if (e.data === YT.PlayerState.ENDED) {
          playNext();
        }
      }
    }
  });

  await loadPlaylist();
}

/* ===============================
   PLAYBACK
   =============================== */
function playNext() {
  if (!player || !playlist.length) return;

  // save current index for "previous"
  if (currentIndex < playlist.length) {
    history.push(currentIndex);
  }

  const track = playlist[currentIndex % playlist.length];
  currentIndex++;

  player.loadVideoById(track.youtube_id);

  const np = document.getElementById("nowPlaying");
  if (np) np.textContent = track.title || "Playing…";
}

function playPrevious() {
  if (!player || history.length === 0) return;

  const prevIndex = history.pop();
  currentIndex = prevIndex;

  const track = playlist[currentIndex];
  player.loadVideoById(track.youtube_id);

  const np = document.getElementById("nowPlaying");
  if (np) np.textContent = track.title || "Playing…";
}

/* ===============================
   DOM READY
   =============================== */
document.addEventListener("DOMContentLoaded", () => {

  // user info
  fetch("api/me.php")
    .then(r => r.json())
    .then(d => {
      const u = document.getElementById("username");
      if (u) u.textContent = d.username;
      if (d.background_image) {
      document.body.style.backgroundImage =
        `url('${d.background_image}')`;
      document.body.style.backgroundSize = "cover";
      document.body.style.backgroundPosition = "center";

      blurEnabled = !!d.bg_blur;
      dimSlider.value = d.bg_dim || 0;
      applyBgEffects();
    }
    });

  // logout
  const logoutBtn = document.getElementById("logout");
  if (logoutBtn) {
    logoutBtn.onclick = async () => {
      await fetch("api/logout.php");
      location.href = "login.html";
    };
  }

  // play
  document.getElementById("playBtn")?.addEventListener("click", async () => {
    await ensurePlayer();

    const state = player.getPlayerState();
    if (state === YT.PlayerState.PAUSED) {
      player.playVideo();
    } else if (state !== YT.PlayerState.PLAYING) {
      playNext();
    }
  });

  // pause
  document.getElementById("pauseBtn")?.addEventListener("click", () => {
    if (player) player.pauseVideo();
  });

  // skip
  document.getElementById("skipBtn")?.addEventListener("click", async () => {
    await ensurePlayer();
    playNext();
  });

  // previous
  document.getElementById("prevBtn")?.addEventListener("click", async () => {
    await ensurePlayer();
    playPrevious();
  });

  const overlay = document.getElementById("bgOverlay");
const blurBtn = document.getElementById("blurToggle");
const dimSlider = document.getElementById("dimSlider");

let blurEnabled = false;

// apply settings visually
function applyBgEffects() {
  overlay.style.backdropFilter = blurEnabled ? "blur(12px)" : "none";
  overlay.style.background =
    `rgba(0, 0, 0, ${dimSlider.value / 100})`;
}

// toggle blur
blurBtn?.addEventListener("click", async () => {
  blurEnabled = !blurEnabled;
  applyBgEffects();

  await fetch("api/update_bg_effects.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      blur: blurEnabled ? 1 : 0,
      dim: dimSlider.value
    })
  });
});

// dim slider
dimSlider?.addEventListener("input", async () => {
  applyBgEffects();

  await fetch("api/update_bg_effects.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      blur: blurEnabled ? 1 : 0,
      dim: dimSlider.value
    })
  });
});

});

// background change
document.getElementById("bgUpload")?.addEventListener("change", async e => {
  const file = e.target.files[0];
  if (!file) return;

  const form = new FormData();
  form.append("image", file);

  const res = await fetch("api/upload_background.php", {
    method: "POST",
    body: form
  });

  const data = await res.json();

  if (!res.ok) {
    alert(data.error || "Upload failed");
    return;
  }

  // apply instantly
  document.body.style.backgroundImage = `url('${data.path}')`;
});

/* ===============================
   HELPERS
   =============================== */
function shuffle(arr) {
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
}
