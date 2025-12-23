<?php
session_start();
if (!isset($_SESSION["user_id"])) {
  header("Location: login.html");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Music Library</title>
  <link rel="stylesheet" href="music.css">
</head>
<body>

<div class="music-container">
    <p id="musicPoints">Music points: …</p>

  <h2>Your Music Library</h2>
  <p>Add a YouTube link (costs 1 music point)</p>

  <div class="music-add">
    <input id="ytLink" placeholder="Paste YouTube link">
    <button onclick="addSong()">Add</button>
  </div>

  <ul id="musicList"></ul>

</div>


<script src="music.js"></script>
</body>
</html>
