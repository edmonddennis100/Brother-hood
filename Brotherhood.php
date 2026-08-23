<?php
/**
 * LDMIS — Advanced Team Formation & Tactical Board
 * -------------------------------------------------
 * Standalone tactical-board page.
 *
 * Integration:
 *   - Keep this file in your LDMIS admin/HR module.
 *   - If you already have authentication/config includes, add them above the
 *     HTML section.
 *   - The board stores formations locally in the browser and can export/import
 *     a complete tactical plan as JSON.
 *
 * No database schema is assumed, so this page will not alter your existing DB.
 */
session_start();

$team_name = $_SESSION['ldmis_team_name'] ?? 'LDMIS TACTICAL UNIT';
$season = date('Y');
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= htmlspecialchars($team_name) ?> — Tactical Board</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Orbitron:wght@500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#07100b;
  --panel:#0c1710;
  --panel-2:#101e14;
  --panel-3:#15271a;
  --line:rgba(255,255,255,.09);
  --text:#edf7ee;
  --muted:#93a49a;
  --gold:#c9a84c;
  --gold-2:#f0d681;
  --pitch:#3f8f1e;
  --pitch-dark:#337617;
  --pitch-light:#4b9f27;
  --home:#e53935;
  --away:#f5f5f5;
  --cyan:#4dd9ff;
  --success:#46d369;
  --danger:#ff5c67;
  --shadow:0 18px 60px rgba(0,0,0,.35);
}
*{box-sizing:border-box}
html,body{margin:0;min-height:100%;background:radial-gradient(circle at 20% 0%,#17261a 0,#07100b 42%,#040806 100%);color:var(--text);font-family:Inter,system-ui,sans-serif}
button,input,select{font:inherit}
button{cursor:pointer}
.app{min-height:100vh;display:grid;grid-template-rows:auto 1fr}
.topbar{
  height:72px;padding:0 20px;display:flex;align-items:center;justify-content:space-between;
  border-bottom:1px solid var(--line);background:rgba(6,13,9,.9);backdrop-filter:blur(18px);
  position:sticky;top:0;z-index:100;
}
.brand{display:flex;align-items:center;gap:12px}
.brand-mark{width:42px;height:42px;border:1px solid rgba(201,168,76,.45);border-radius:12px;display:grid;place-items:center;background:linear-gradient(145deg,#1b2d1d,#0b120d);color:var(--gold);box-shadow:inset 0 0 20px rgba(201,168,76,.08)}
.brand h1{font:700 15px Orbitron,sans-serif;letter-spacing:1.5px;margin:0}
.brand small{display:block;color:var(--muted);font-size:10px;margin-top:3px;letter-spacing:.7px}
.toolbar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;justify-content:flex-end}
.btn{
  border:1px solid var(--line);background:#101a13;color:var(--text);padding:9px 12px;border-radius:9px;
  transition:.18s;display:inline-flex;align-items:center;gap:7px;font-size:12px;font-weight:700;
}
.btn:hover{border-color:rgba(201,168,76,.5);transform:translateY(-1px);background:#152219}
.btn.gold{background:linear-gradient(135deg,#c9a84c,#9f7e2f);color:#111;border:0}
.btn.green{background:rgba(70,211,105,.1);border-color:rgba(70,211,105,.3)}
.btn.danger{background:rgba(255,92,103,.08);border-color:rgba(255,92,103,.28)}
.layout{
  width:min(1700px,100%);margin:auto;padding:18px;display:grid;grid-template-columns:270px minmax(480px,1fr) 300px;
  gap:16px;align-items:start;
}
.panel{background:linear-gradient(180deg,rgba(16,30,20,.97),rgba(9,18,12,.97));border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);overflow:hidden}
.panel-head{padding:14px 15px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center}
.panel-head h2{font-size:12px;margin:0;letter-spacing:1.1px;text-transform:uppercase}
.panel-body{padding:13px}
.field{margin-bottom:11px}
.field label{display:block;color:var(--muted);font-size:10px;text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px}
.field input,.field select{
  width:100%;border:1px solid var(--line);background:#0a130d;color:var(--text);border-radius:9px;padding:10px 11px;outline:0;
}
.field input:focus,.field select:focus{border-color:rgba(201,168,76,.65);box-shadow:0 0 0 3px rgba(201,168,76,.07)}
.section-title{font-size:10px;color:var(--gold-2);text-transform:uppercase;letter-spacing:1px;margin:18px 0 9px}
.formation-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:6px}
.formation-btn{padding:9px 4px;border-radius:8px;border:1px solid var(--line);background:#0b150e;color:#c7d2ca;font-size:10px;font-weight:700}
.formation-btn.active{border-color:var(--gold);color:var(--gold-2);background:rgba(201,168,76,.1)}
.player-list{display:flex;flex-direction:column;gap:6px;max-height:340px;overflow:auto;padding-right:3px}
.player-row{display:grid;grid-template-columns:28px 1fr auto;gap:7px;align-items:center;padding:7px;border:1px solid var(--line);border-radius:9px;background:#0a130d}
.player-row .num{width:28px;height:28px;border-radius:7px;display:grid;place-items:center;background:#1b2a1d;color:var(--gold-2);font-weight:800;font-size:10px}
.player-row input{min-width:0;background:transparent;border:0;outline:0;color:var(--text);font-size:11px}
.icon-btn{border:0;background:transparent;color:var(--muted);padding:5px}.icon-btn:hover{color:#fff}
.pitch-wrap{display:flex;justify-content:center;min-width:0}
.board{
  position:relative;width:min(720px,100%);aspect-ratio:824/1359;min-height:520px;
  border:5px solid rgba(255,255,255,.82);border-radius:3px;overflow:hidden;
  background:
    repeating-linear-gradient(to bottom,rgba(255,255,255,.025) 0,rgba(255,255,255,.025) 7.14%,transparent 7.14%,transparent 14.28%),
    linear-gradient(90deg,var(--pitch-dark),var(--pitch),var(--pitch-dark));
  box-shadow:0 25px 80px rgba(0,0,0,.5),inset 0 0 80px rgba(0,0,0,.15);
  touch-action:none;
}
.pitch-svg{position:absolute;inset:0;width:100%;height:100%;pointer-events:none}
.tactical-layer{position:absolute;inset:0;z-index:3;pointer-events:none}
.draw-canvas{position:absolute;inset:0;width:100%;height:100%;z-index:4;pointer-events:none}
.player{
  position:absolute;transform:translate(-50%,-50%);width:76px;height:86px;display:flex;flex-direction:column;align-items:center;
  user-select:none;touch-action:none;cursor:grab;z-index:8;pointer-events:auto;
}
.player:active{cursor:grabbing}
.player.selected{filter:drop-shadow(0 0 10px rgba(255,255,255,.7))}
.player-shirt{position:relative;width:48px;height:48px;border-radius:9px 9px 12px 12px;background:var(--home);box-shadow:0 5px 12px rgba(0,0,0,.28);border:1px solid rgba(0,0,0,.2)}
.player-shirt:before,.player-shirt:after{content:"";position:absolute;top:3px;width:17px;height:28px;background:inherit}
.player-shirt:before{left:-12px;transform:rotate(23deg);border-radius:7px}
.player-shirt:after{right:-12px;transform:rotate(-23deg);border-radius:7px}
.player-shirt .number{position:absolute;inset:0;display:grid;place-items:center;font-size:13px;font-weight:900;color:#fff;z-index:2}
.player-name{margin-top:7px;max-width:105px;background:rgba(255,255,255,.92);color:#172019;padding:3px 7px;border-radius:3px;font-size:10px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;box-shadow:0 2px 8px rgba(0,0,0,.22)}
.player-badge{position:absolute;right:-3px;top:0;background:#0c1710;color:#fff;border:1px solid rgba(255,255,255,.25);font-size:8px;padding:2px 4px;border-radius:5px}
.player.away .player-shirt{background:var(--away)}
.player.away .player-shirt .number{color:#333}
.player.away .player-name{background:rgba(255,255,255,.96)}
.player.ghost{opacity:.45}
.pitch-hud{position:absolute;z-index:10;left:10px;right:10px;top:10px;display:flex;justify-content:space-between;pointer-events:none}
.hud-chip{font:700 9px Orbitron,sans-serif;letter-spacing:1px;background:rgba(3,10,5,.65);border:1px solid rgba(255,255,255,.15);padding:7px 9px;border-radius:7px;backdrop-filter:blur(7px)}
.center-label{position:absolute;z-index:6;left:50%;top:50%;transform:translate(-50%,-50%);font:700 9px Orbitron,sans-serif;letter-spacing:2px;color:rgba(255,255,255,.45);pointer-events:none}
.modebar{display:flex;gap:6px;flex-wrap:wrap}
.modebar .btn.active{border-color:var(--cyan);color:var(--cyan);background:rgba(77,217,255,.07)}
.swatches{display:flex;gap:7px}
.swatch{width:25px;height:25px;border-radius:7px;border:2px solid transparent}
.swatch.active{border-color:#fff;box-shadow:0 0 0 2px rgba(255,255,255,.18)}
.timeline{margin-top:12px;border:1px solid var(--line);border-radius:12px;padding:10px;background:#09120c}
.timeline-row{display:flex;align-items:center;gap:7px}
.timeline input[type=range]{flex:1;accent-color:var(--gold)}
.timeline-time{font:700 10px Orbitron,sans-serif;color:var(--gold-2);min-width:38px;text-align:right}
.keyframes{display:flex;gap:4px;margin-top:8px;overflow:auto}.keyframe{min-width:40px;padding:5px;border-radius:6px;border:1px solid var(--line);background:#0d1a11;color:var(--muted);font-size:9px;text-align:center}
.keyframe.active{border-color:var(--gold);color:var(--gold)}
.stats{display:grid;grid-template-columns:repeat(2,1fr);gap:7px}
.stat{padding:9px;border:1px solid var(--line);border-radius:9px;background:#09120c}.stat b{display:block;font:700 15px Orbitron,sans-serif}.stat span{font-size:9px;color:var(--muted);text-transform:uppercase}
.toggle{display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid rgba(255,255,255,.05);font-size:11px}.toggle:last-child{border-bottom:0}
.toggle input{accent-color:var(--gold)}
.toast{position:fixed;right:18px;bottom:18px;z-index:500;padding:12px 15px;border-radius:10px;background:#101e14;border:1px solid rgba(201,168,76,.35);box-shadow:var(--shadow);font-size:12px;display:none}
.modal{position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(8px);display:none;align-items:center;justify-content:center;z-index:300;padding:18px}
.modal.show{display:flex}.modal-card{width:min(520px,100%);background:#0d1911;border:1px solid var(--line);border-radius:16px;box-shadow:var(--shadow);overflow:hidden}
.modal-head,.modal-foot{padding:14px 16px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center}.modal-foot{border-top:1px solid var(--line);border-bottom:0}
.modal-head h3{font-size:13px;margin:0}.modal-body{padding:16px}
.file-drop{border:1px dashed rgba(201,168,76,.45);border-radius:12px;padding:20px;text-align:center;color:var(--muted);font-size:11px}
@media(max-width:1180px){.layout{grid-template-columns:230px minmax(430px,1fr)}.right-panel{grid-column:1/-1;display:grid;grid-template-columns:repeat(3,1fr);gap:12px}.right-panel .panel{min-width:0}}
@media(max-width:820px){.topbar{height:auto;min-height:68px;padding:10px 12px;gap:10px;align-items:flex-start}.toolbar{gap:5px}.toolbar .btn span{display:none}.layout{grid-template-columns:1fr;padding:10px}.left-panel,.right-panel{display:none}.board{width:min(100%,520px)}.pitch-wrap{width:100%}}
@media print{
  @page{size:A4 portrait;margin:8mm}
  body{background:#fff;color:#111}
  .topbar,.left-panel,.right-panel,.timeline,.no-print{display:none!important}
  .layout{display:block;padding:0}
  .pitch-wrap{display:block}
  .board{width:100%;max-width:190mm;min-height:0;margin:auto;box-shadow:none;border-color:#111;print-color-adjust:exact;-webkit-print-color-adjust:exact}
}
</style>
</head>
<body>
<div class="app">
<header class="topbar">
  <div class="brand">
    <div class="brand-mark">⚽</div>
    <div><h1>TACTICAL BOARD</h1><small><?= htmlspecialchars($team_name) ?> · <?= $season ?> · LDMIS</small></div>
  </div>
  <div class="toolbar no-print">
    <button class="btn" onclick="newBoard()">＋ <span>New Plan</span></button>
    <button class="btn" onclick="openImport()">⇧ <span>Import</span></button>
    <button class="btn" onclick="exportPlan()">⇩ <span>Export</span></button>
    <button class="btn" onclick="window.print()">⎙ <span>Print</span></button>
    <button class="btn gold" onclick="savePlan()">✓ <span>Save Formation</span></button>
  </div>
</header>

<main class="layout">
  <aside class="panel left-panel no-print">
    <div class="panel-head"><h2>Formation</h2><span style="color:var(--gold)">01</span></div>
    <div class="panel-body">
      <div class="field"><label>Plan name</label><input id="planName" value="Match Day Formation"></div>
      <div class="field"><label>Opponent</label><input id="opponent" placeholder="Enter opponent"></div>
      <div class="field"><label>Formation</label>
        <select id="formationSelect" onchange="applyFormation(this.value)">
          <option value="4-3-3">4-3-3 Attack</option><option value="4-4-2">4-4-2 Balanced</option>
          <option value="4-2-3-1">4-2-3-1</option><option value="3-5-2">3-5-2</option>
          <option value="3-4-3">3-4-3</option><option value="5-3-2">5-3-2</option>
          <option value="4-1-4-1">4-1-4-1</option>
        </select>
      </div>
      <div class="section-title">Quick formations</div>
      <div class="formation-grid">
        <?php foreach(['4-3-3','4-4-2','4-2-3-1','3-5-2','3-4-3','5-3-2'] as $f): ?>
          <button class="formation-btn" data-formation="<?= $f ?>" onclick="applyFormation('<?= $f ?>')"><?= $f ?></button>
        <?php endforeach; ?>
      </div>
      <div class="section-title">Players</div>
      <div class="player-list" id="playerList"></div>
      <button class="btn green" style="width:100%;justify-content:center;margin-top:9px" onclick="addPlayer()">＋ Add Player</button>
      <div class="section-title">Kit</div>
      <div class="swatches" id="swatches">
        <button class="swatch active" style="background:#e53935" data-color="#e53935"></button>
        <button class="swatch" style="background:#1976d2" data-color="#1976d2"></button>
        <button class="swatch" style="background:#111" data-color="#111"></button>
        <button class="swatch" style="background:#f5c400" data-color="#f5c400"></button>
        <button class="swatch" style="background:#fff;border-color:#777" data-color="#fff"></button>
      </div>
    </div>
  </aside>

  <section>
    <div class="pitch-wrap">
      <div class="board" id="board">
        <svg class="pitch-svg" viewBox="0 0 824 1359" preserveAspectRatio="none" aria-hidden="true">
          <g fill="none" stroke="rgba(255,255,255,.86)" stroke-width="4">
            <rect x="12" y="12" width="800" height="1335" rx="2"/>
            <line x1="12" y1="679.5" x2="812" y2="679.5"/>
            <circle cx="412" cy="679.5" r="94"/><circle cx="412" cy="679.5" r="4" fill="white"/>
            <rect x="159" y="12" width="506" height="198"/><rect x="267" y="12" width="290" height="86"/>
            <path d="M318 210 A94 94 0 0 0 506 210"/>
            <rect x="159" y="1149" width="506" height="198"/><rect x="267" y="1255" width="290" height="92"/>
            <path d="M318 1149 A94 94 0 0 1 506 1149"/>
            <path d="M12 55 Q55 55 55 12 M812 55 Q769 55 769 12 M12 1304 Q55 1304 55 1347 M812 1304 Q769 1304 769 1347"/>
          </g>
          <g fill="rgba(255,255,255,.85)">
            <circle cx="412" cy="210" r="4"/><circle cx="412" cy="1149" r="4"/>
          </g>
        </svg>
        <div class="pitch-hud no-print"><span class="hud-chip" id="hudFormation">4-3-3 ATTACK</span><span class="hud-chip" id="hudPlan">TACTICAL MODE</span></div>
        <div class="center-label">LDMIS · TACTICAL ANALYSIS</div>
        <svg id="arrowSvg" class="tactical-layer" viewBox="0 0 100 165" preserveAspectRatio="none"></svg>
        <canvas id="drawCanvas" class="draw-canvas"></canvas>
      </div>
    </div>

    <div class="timeline no-print">
      <div class="modebar">
        <button class="btn active" id="selectMode" onclick="setMode('select')">↖ Select / Move</button>
        <button class="btn" id="drawMode" onclick="setMode('draw')">✎ Draw</button>
        <button class="btn" id="arrowMode" onclick="setMode('arrow')">➜ Movement Arrow</button>
        <button class="btn danger" onclick="clearTactics()">⌫ Clear Drawings</button>
      </div>
      <div class="timeline-row" style="margin-top:10px">
        <button class="btn" onclick="togglePlay()">▶</button>
        <input id="timeRange" type="range" min="0" max="100" value="0" oninput="scrub(this.value)">
        <div class="timeline-time" id="timeLabel">00:00</div>
      </div>
      <div class="keyframes" id="keyframes">
        <div class="keyframe active">START</div><div class="keyframe">10s</div><div class="keyframe">20s</div><div class="keyframe">30s</div><div class="keyframe">40s</div><div class="keyframe">50s</div>
      </div>
    </div>
  </section>

  <aside class="right-panel no-print">
    <div class="panel">
      <div class="panel-head"><h2>Selected Player</h2><span id="selectedNo">—</span></div>
      <div class="panel-body">
        <div class="field"><label>Name</label><input id="selectedName" placeholder="Select a player" oninput="updateSelectedName(this.value)"></div>
        <div class="field"><label>Role / Position</label><select id="selectedRole" onchange="updateSelectedRole(this.value)">
          <option>Goalkeeper</option><option>Defender</option><option>Wing Back</option><option>Midfielder</option><option>Attacker</option><option>Striker</option>
        </select></div>
        <div class="field"><label>Number</label><input id="selectedNumber" type="number" min="1" max="99" oninput="updateSelectedNumber(this.value)"></div>
        <div class="toggle"><span>Show player label</span><input id="showLabels" type="checkbox" checked onchange="renderPlayers()"></div>
        <div class="toggle"><span>Show role badge</span><input id="showRoles" type="checkbox" checked onchange="renderPlayers()"></div>
      </div>
    </div>
    <div class="panel">
      <div class="panel-head"><h2>Board Controls</h2><span style="color:var(--cyan)">LIVE</span></div>
      <div class="panel-body">
        <div class="toggle"><span>Grid / pitch lines</span><input type="checkbox" checked onchange="togglePitch(this.checked)"></div>
        <div class="toggle"><span>Movement trail</span><input type="checkbox" checked id="trailToggle"></div>
        <div class="toggle"><span>Opposition players</span><input type="checkbox" checked id="awayToggle" onchange="toggleAway(this.checked)"></div>
        <div class="toggle"><span>Auto animation</span><input type="checkbox" id="autoAnim"></div>
        <button class="btn" style="width:100%;justify-content:center;margin-top:12px" onclick="centerPlayers()">◎ Reset positions</button>
      </div>
    </div>
    <div class="panel">
      <div class="panel-head"><h2>Squad Overview</h2></div>
      <div class="panel-body">
        <div class="stats">
          <div class="stat"><b id="statPlayers">11</b><span>Players</span></div>
          <div class="stat"><b id="statDef">4</b><span>Defenders</span></div>
          <div class="stat"><b id="statMid">3</b><span>Midfield</span></div>
          <div class="stat"><b id="statAtk">3</b><span>Attack</span></div>
        </div>
      </div>
    </div>
  </aside>
</main>
</div>

<div class="toast" id="toast"></div>

<div class="modal" id="importModal">
  <div class="modal-card">
    <div class="modal-head"><h3>Import Tactical Plan</h3><button class="icon-btn" onclick="closeImport()">✕</button></div>
    <div class="modal-body">
      <div class="file-drop" onclick="document.getElementById('planFile').click()">
        <div style="font-size:28px;margin-bottom:7px">⇧</div>
        Select a previously exported <b>.json</b> tactical plan.
        <input id="planFile" type="file" accept=".json,application/json" hidden onchange="importPlan(this.files[0])">
      </div>
    </div>
    <div class="modal-foot"><button class="btn" onclick="closeImport()">Cancel</button></div>
  </div>
</div>

<script>
const STORAGE_KEY='ldmis_tactical_board_v1';
const board=document.getElementById('board');
let mode='select', selectedId=null, drawing=false, drawPoints=[], arrowStart=null, playing=false, playTimer=null;
let kitColor='#e53935';
let players=[];
const formationTemplates={
 '4-3-3':[
  ['GK','Goalkeeper',50,8],['LB','Defender',18,23],['CB','Defender',39,25],['CB','Defender',61,25],['RB','Defender',82,23],
  ['CM','Midfielder',25,39],['CM','Midfielder',50,42],['CM','Midfielder',75,39],
  ['LW','Attacker',18,57],['ST','Attacker',50,61],['RW','Attacker',82,57]
 ],
 '4-4-2':[
  ['GK','Goalkeeper',50,8],['LB','Defender',18,23],['CB','Defender',39,25],['CB','Defender',61,25],['RB','Defender',82,23],
  ['LM','Midfielder',16,40],['CM','Midfielder',39,43],['CM','Midfielder',61,43],['RM','Midfielder',84,40],
  ['ST','Attacker',37,60],['ST','Attacker',63,60]
 ],
 '4-2-3-1':[
  ['GK','Goalkeeper',50,8],['LB','Defender',18,23],['CB','Defender',39,25],['CB','Defender',61,25],['RB','Defender',82,23],
  ['DM','Midfielder',38,38],['DM','Midfielder',62,38],['LW','Attacker',20,49],['AM','Attacker',50,48],['RW','Attacker',80,49],['ST','Attacker',50,62]
 ],
 '3-5-2':[
  ['GK','Goalkeeper',50,8],['CB','Defender',30,24],['CB','Defender',50,22],['CB','Defender',70,24],
  ['LWB','Midfielder',10,39],['CM','Midfielder',32,41],['DM','Midfielder',50,38],['CM','Midfielder',68,41],['RWB','Midfielder',90,39],
  ['ST','Attacker',38,59],['ST','Attacker',62,59]
 ],
 '3-4-3':[
  ['GK','Goalkeeper',50,8],['CB','Defender',30,24],['CB','Defender',50,22],['CB','Defender',70,24],
  ['LM','Midfielder',18,40],['CM','Midfielder',40,42],['CM','Midfielder',60,42],['RM','Midfielder',82,40],
  ['LW','Attacker',20,59],['ST','Attacker',50,62],['RW','Attacker',80,59]
 ],
 '5-3-2':[
  ['GK','Goalkeeper',50,8],['LWB','Defender',10,27],['CB','Defender',30,25],['CB','Defender',50,23],['CB','Defender',70,25],['RWB','Defender',90,27],
  ['CM','Midfielder',30,41],['CM','Midfielder',50,43],['CM','Midfielder',70,41],['ST','Attacker',38,61],['ST','Attacker',62,61]
 ],
 '4-1-4-1':[
  ['GK','Goalkeeper',50,8],['LB','Defender',18,23],['CB','Defender',39,25],['CB','Defender',61,25],['RB','Defender',82,23],
  ['DM','Midfielder',50,37],['LM','Midfielder',15,46],['CM','Midfielder',38,45],['CM','Midfielder',62,45],['RM','Midfielder',85,46],['ST','Attacker',50,62]
 ]
};
const defaultNames=['Goalkeeper','Left Defender','Center Back','Center Back','Right Defender','Left Midfielder','Central Midfielder','Right Midfielder','Left Attacker','Striker','Right Attacker'];

function uid(){return 'p_'+Math.random().toString(36).slice(2,9)}
function toast(msg){const t=document.getElementById('toast');t.textContent=msg;t.style.display='block';clearTimeout(t._x);t._x=setTimeout(()=>t.style.display='none',2200)}
function makePlayers(f='4-3-3'){
 return formationTemplates[f].map((x,i)=>({id:uid(),name:defaultNames[i]||x[0],role:x[1],number:i+1,x:x[2],y:x[3],team:'home',visible:true}))
}
function applyFormation(f){
 document.getElementById('formationSelect').value=f;
 players=makePlayers(f);
 selectedId=null;
 document.querySelectorAll('.formation-btn').forEach(b=>b.classList.toggle('active',b.dataset.formation===f));
 document.getElementById('hudFormation').textContent=f+' · '+(f==='4-3-3'?'ATTACK':'TACTICAL');
 renderAll(); saveSilent(); toast('Formation changed to '+f);
}
function renderAll(){renderPlayers();renderList();updateStats();updateInspector();resizeCanvas()}
function renderPlayers(){
 document.querySelectorAll('.player').forEach(e=>e.remove());
 players.filter(p=>p.visible!==false).forEach(p=>{
   const el=document.createElement('div');el.className='player '+(p.team==='away'?'away':'')+(p.id===selectedId?' selected':'');
   el.dataset.id=p.id;el.style.left=p.x+'%';el.style.top=p.y+'%';
   el.innerHTML='<div class="player-shirt"><span class="number">'+esc(p.number)+'</span></div>'+
     '<div class="player-name" style="display:'+(document.getElementById('showLabels').checked?'block':'none')+'">'+esc(p.name)+'</div>'+
     '<div class="player-badge" style="display:'+(document.getElementById('showRoles').checked?'block':'none')+'">'+esc(p.role)+'</div>';
   el.addEventListener('pointerdown',startDrag);el.addEventListener('click',()=>selectPlayer(p.id));
   board.appendChild(el);
 });
}
function renderList(){
 const box=document.getElementById('playerList');box.innerHTML='';
 players.forEach(p=>{
   const row=document.createElement('div');row.className='player-row';
   row.innerHTML='<div class="num">'+esc(p.number)+'</div><input value="'+escAttr(p.name)+'"><button class="icon-btn" title="Select">↗</button>';
   row.querySelector('input').addEventListener('input',e=>{p.name=e.target.value;renderPlayers();updateInspector()});
   row.querySelector('button').onclick=()=>selectPlayer(p.id);
   box.appendChild(row);
 });
}
function selectPlayer(id){selectedId=id;renderPlayers();updateInspector()}
function updateInspector(){
 const p=players.find(x=>x.id===selectedId);
 document.getElementById('selectedNo').textContent=p?'#'+p.number:'—';
 document.getElementById('selectedName').value=p?p.name:'';
 document.getElementById('selectedRole').value=p?p.role:'Goalkeeper';
 document.getElementById('selectedNumber').value=p?p.number:'';
}
function updateSelectedName(v){const p=players.find(x=>x.id===selectedId);if(p){p.name=v;renderPlayers();renderList()}}
function updateSelectedRole(v){const p=players.find(x=>x.id===selectedId);if(p){p.role=v;renderPlayers();updateStats()}}
function updateSelectedNumber(v){const p=players.find(x=>x.id===selectedId);if(p){p.number=Math.max(1,Math.min(99,+v||1));renderPlayers();renderList()}}
function addPlayer(){
 if(players.length>=22){toast('Maximum tactical board capacity reached');return}
 players.push({id:uid(),name:'New Player',role:'Midfielder',number:players.length+1,x:50,y:50,team:'home',visible:true});
 renderAll();selectPlayer(players.at(-1).id)
}
function removeSelected(){
 if(!selectedId)return;
 players=players.filter(p=>p.id!==selectedId);selectedId=null;renderAll();
}
function startDrag(e){
 if(mode!=='select')return;
 const el=e.currentTarget,p=players.find(x=>x.id===el.dataset.id);if(!p)return;
 selectedId=p.id;renderPlayers();
 el.setPointerCapture(e.pointerId);
 const rect=board.getBoundingClientRect();
 const move=ev=>{p.x=Math.max(3,Math.min(97,(ev.clientX-rect.left)/rect.width*100));p.y=Math.max(3,Math.min(97,(ev.clientY-rect.top)/rect.height*100));el.style.left=p.x+'%';el.style.top=p.y+'%';drawMovementTrail(p)};
 const up=()=>{el.removeEventListener('pointermove',move);el.removeEventListener('pointerup',up);saveSilent();renderList();};
 el.addEventListener('pointermove',move);el.addEventListener('pointerup',up);
}
function drawMovementTrail(p){/* reserved for future keyframe interpolation */}
function setMode(m){
 mode=m;drawing=false;arrowStart=null;drawPoints=[];
 ['selectMode','drawMode','arrowMode'].forEach(id=>document.getElementById(id).classList.remove('active'));
 document.getElementById(m==='select'?'selectMode':m==='draw'?'drawMode':'arrowMode').classList.add('active');
 board.style.cursor=m==='select'?'default':m==='draw'?'crosshair':'crosshair';
}
function resizeCanvas(){const c=document.getElementById('drawCanvas'),r=board.getBoundingClientRect(),d=devicePixelRatio||1;c.width=r.width*d;c.height=r.height*d;const x=c.getContext('2d');x.setTransform(d,0,0,d,0,0)}
function boardPoint(e){const r=board.getBoundingClientRect();return{x:e.clientX-r.left,y:e.clientY-r.top,xp:(e.clientX-r.left)/r.width*100,yp:(e.clientY-r.top)/r.height*100}}
board.addEventListener('pointerdown',e=>{
 if(mode==='select'||e.target.closest('.player'))return;
 const pt=boardPoint(e);
 if(mode==='draw'){drawing=true;drawPoints=[pt];}
 if(mode==='arrow'){if(!arrowStart){arrowStart=pt}else{drawArrow(arrowStart,pt);arrowStart=null}}
});
board.addEventListener('pointermove',e=>{
 if(mode!=='draw'||!drawing)return;const pt=boardPoint(e);drawPoints.push(pt);
 const c=document.getElementById('drawCanvas'),ctx=c.getContext('2d');ctx.lineWidth=3;ctx.lineCap='round';ctx.strokeStyle='#ffe27a';ctx.beginPath();
 drawPoints.forEach((p,i)=>i?ctx.lineTo(p.x,p.y):ctx.moveTo(p.x,p.y));ctx.stroke();
});
board.addEventListener('pointerup',()=>{if(mode==='draw'){drawing=false;saveSilent()}});
function drawArrow(a,b){
 const svg=document.getElementById('arrowSvg'),ns='http://www.w3.org/2000/svg';
 if(!svg.querySelector('defs')){const d=document.createElementNS(ns,'defs');d.innerHTML='<marker id="arr" markerWidth="7" markerHeight="7" refX="5" refY="3.5" orient="auto"><path d="M0,0 L7,3.5 L0,7 z" fill="#4dd9ff"/></marker>';svg.appendChild(d)}
 const l=document.createElementNS(ns,'line');l.setAttribute('x1',a.xp);l.setAttribute('y1',a.yp);l.setAttribute('x2',b.xp);l.setAttribute('y2',b.yp);l.setAttribute('stroke','#4dd9ff');l.setAttribute('stroke-width','0.65');l.setAttribute('marker-end','url(#arr)');l.setAttribute('opacity','.95');svg.appendChild(l);saveSilent()
}
function clearTactics(){document.getElementById('arrowSvg').innerHTML='';const c=document.getElementById('drawCanvas');c.getContext('2d').clearRect(0,0,c.width,c.height);toast('Tactical drawings cleared')}
function togglePitch(show){document.querySelector('.pitch-svg').style.opacity=show?'1':'0'}
function toggleAway(show){players.filter(p=>p.team==='away').forEach(p=>p.visible=show);renderPlayers()}
function centerPlayers(){const f=document.getElementById('formationSelect').value;players=makePlayers(f);renderAll();toast('Formation positions reset')}
function updateStats(){
 document.getElementById('statPlayers').textContent=players.length;
 document.getElementById('statDef').textContent=players.filter(p=>['Defender','Wing Back'].includes(p.role)).length;
 document.getElementById('statMid').textContent=players.filter(p=>p.role==='Midfielder').length;
 document.getElementById('statAtk').textContent=players.filter(p=>['Attacker','Striker'].includes(p.role)).length;
}
function changeKit(c){kitColor=c;document.documentElement.style.setProperty('--home',c);document.querySelectorAll('.swatch').forEach(s=>s.classList.toggle('active',s.dataset.color===c));}
document.querySelectorAll('.swatch').forEach(s=>s.onclick=()=>changeKit(s.dataset.color));
function newBoard(){if(confirm('Start a new tactical plan? Unsaved changes will be replaced.')){players=makePlayers('4-3-3');document.getElementById('planName').value='Match Day Formation';document.getElementById('opponent').value='';clearTactics();renderAll()}}
function payload(){return {version:1,team:'<?= addslashes($team_name) ?>',planName:document.getElementById('planName').value,opponent:document.getElementById('opponent').value,formation:document.getElementById('formationSelect').value,kitColor,players,exportedAt:new Date().toISOString()}}
function saveSilent(){localStorage.setItem(STORAGE_KEY,JSON.stringify(payload()))}
function savePlan(){saveSilent();toast('Tactical plan saved on this device')}
function exportPlan(){
 const blob=new Blob([JSON.stringify(payload(),null,2)],{type:'application/json'}),a=document.createElement('a');
 a.href=URL.createObjectURL(blob);a.download=(document.getElementById('planName').value||'ldmis-tactical-plan').replace(/[^a-z0-9_-]+/gi,'_')+'.json';a.click();URL.revokeObjectURL(a.href);toast('Tactical plan exported')}
function openImport(){document.getElementById('importModal').classList.add('show')}
function closeImport(){document.getElementById('importModal').classList.remove('show')}
function importPlan(file){if(!file)return;const r=new FileReader();r.onload=()=>{try{const d=JSON.parse(r.result);players=d.players||makePlayers(d.formation||'4-3-3');document.getElementById('planName').value=d.planName||'Imported Plan';document.getElementById('opponent').value=d.opponent||'';document.getElementById('formationSelect').value=d.formation||'4-3-3';changeKit(d.kitColor||'#e53935');renderAll();closeImport();toast('Tactical plan imported')}catch(e){toast('Invalid tactical plan file')}};r.readAsText(file)}
function scrub(v){document.getElementById('timeLabel').textContent='00:'+String(Math.round(v*.6)).padStart(2,'0')}
function togglePlay(){playing=!playing;if(playing){playTimer=setInterval(()=>{let r=document.getElementById('timeRange');r.value=(+r.value+1)%101;scrub(r.value)},600)}else clearInterval(playTimer)}
function esc(s){return String(s).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]))}
function escAttr(s){return esc(s).replace(/"/g,'&quot;')}
window.addEventListener('resize',resizeCanvas);

(function init(){
 const saved=localStorage.getItem(STORAGE_KEY);
 if(saved){try{const d=JSON.parse(saved);players=d.players||makePlayers(d.formation||'4-3-3');document.getElementById('planName').value=d.planName||'Match Day Formation';document.getElementById('opponent').value=d.opponent||'';document.getElementById('formationSelect').value=d.formation||'4-3-3';changeKit(d.kitColor||'#e53935')}catch(e){players=makePlayers()}}
 else players=makePlayers();
 document.querySelector('[data-formation="4-3-3"]').classList.add('active');
 renderAll();
})();
</script>
</body>
</html>
