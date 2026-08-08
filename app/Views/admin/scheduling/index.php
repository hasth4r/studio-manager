<?= $this->extend('layouts/dashboard') ?>
<?= $this->section('content') ?>
<style>
:root{
    --dw:38px; --hh:86px; --artist-h:32px;
    --c-bg:transparent;
    --c-card:rgb(var(--color-card));
    --c-border:rgb(var(--color-border));
    --c-text:rgb(var(--color-text));
    --c-muted:rgb(var(--color-muted));
    --c-ai:rgb(var(--color-accent));
}
*{box-sizing:border-box;margin:0;padding:0;}
.g-root{display:flex;flex-direction:column;height:100%;font-family:'DM Sans',sans-serif;background:transparent;}

/* ── TOPBAR ── */
.g-top{height:50px;min-height:50px;background:rgba(var(--color-card),0.5);border-bottom:1px solid var(--c-border);
  display:flex;align-items:center;gap:8px;padding:0 14px;flex-shrink:0;}
.g-logo{font-size:14px;font-weight:700;color:var(--c-text);display:flex;align-items:center;gap:6px;white-space:nowrap;}
.g-logo .ico{color:var(--c-ai);}
.vsep{width:1px;height:24px;background:var(--c-border);margin:0 2px;}
.btn{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;
  padding:5px 12px;border-radius:6px;cursor:pointer;border:none;white-space:nowrap;transition:all 0.2s;}
.btn-ai{background:rgb(var(--color-accent));color:#fff;box-shadow:0 0 14px rgba(var(--color-accent),0.35);}
.btn-ai:hover{background:rgb(var(--color-accent-hover));}
.btn-ai.busy{opacity:.6;pointer-events:none;}
.btn-green{background:rgb(var(--color-success));color:#fff;}
.btn-green:hover{filter:brightness(1.1);}
.btn-ghost{background:transparent;border:1px solid var(--c-border);color:var(--c-muted);}
.btn-ghost:hover{border-color:var(--c-text);color:var(--c-text);}
.btn-save{display:none;} .btn-save.show{display:inline-flex;}
.spc{flex:1;}
.view-toggle{display:flex;background:rgba(var(--color-bg),0.8);border:1px solid var(--c-border);border-radius:6px;overflow:hidden;}
.vt-btn{padding:5px 10px;font-size:11px;font-weight:600;cursor:pointer;border:none;
  background:none;color:var(--c-muted);transition:all .15s;}
.vt-btn.on{background:rgba(var(--color-accent),0.1);color:var(--c-ai);}

/* Project selector pills */
.proj-pills{display:flex;gap:4px;flex-wrap:wrap;}
.proj-pill{display:flex;align-items:center;gap:5px;padding:3px 8px;border-radius:100px;
  font-size:11px;font-weight:700;cursor:pointer;border:2px solid transparent;
  transition:all .15s;white-space:nowrap;}
.proj-pill.on{border-color:rgba(255,255,255,.3);}
.proj-dot{width:7px;height:7px;border-radius:50%;}

/* ── MAIN SPLIT ── */
.g-main{display:flex;flex:1;overflow:hidden;background:transparent;}

/* ── SEARCH DRAWER ── */
.drawer{width:260px;min-width:260px;background:var(--c-card);border-right:1px solid var(--c-border);
  display:flex;flex-direction:column;flex-shrink:0;z-index:20;}
.drawer-search{padding:10px;border-bottom:1px solid var(--c-border);flex-shrink:0;}
.search-box{width:100%;background:rgba(var(--color-bg),0.8);border:1px solid var(--c-border);color:var(--c-text);
  font-size:13px;padding:7px 10px;border-radius:7px;outline:none;}
.search-box:focus{border-color:var(--c-ai);}
.drawer-tabs{display:flex;padding:0 14px;border-bottom:1px solid var(--c-border);flex-shrink:0;}
.dtab{flex:1;padding:7px 0;font-size:11px;font-weight:700;color:var(--c-muted);cursor:pointer;
  border:none;background:none;text-transform:uppercase;letter-spacing:.06em;
  border-bottom:2px solid transparent;transition:color .15s;}
.dtab.on{color:var(--c-ai);border-bottom-color:var(--c-ai);}
.drawer-body{flex:1;overflow-y:auto;padding:6px;}
.drawer-body::-webkit-scrollbar{width:3px;}
.drawer-body::-webkit-scrollbar-thumb{background:var(--c-border);border-radius:3px;}

/* Task card in drawer */
.dcard{background:rgba(var(--color-bg),0.6);border:1px solid var(--c-border);border-radius:7px;padding:8px 9px;
  margin-bottom:5px;cursor:grab;transition:border-color .15s,box-shadow .15s;display:flex;gap:8px;}
.dcard:hover{border-color:rgba(var(--color-accent),0.5);box-shadow:0 2px 12px rgba(0,0,0,.4);}
.dcard:active{cursor:grabbing;}
.dcard.dragging-card{opacity:.6;box-shadow:0 8px 32px rgba(0,0,0,.7);}
.dcard-thumb{width:36px;height:36px;border-radius:5px;object-fit:cover;flex-shrink:0;background:rgba(var(--color-bg),1);}
.dcard-thumb-ph{width:36px;height:36px;border-radius:5px;background:rgba(var(--color-bg),1);flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:12px;color:var(--c-muted);}
.dcard-info{flex:1;min-width:0;}
.dcard-name{font-size:12px;font-weight:700;color:var(--c-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.dcard-sub{font-size:10px;color:var(--c-muted);margin-top:2px;display:flex;gap:5px;align-items:center;flex-wrap:wrap;}
.dcard-proj{font-size:10px;font-weight:700;padding:1px 5px;border-radius:3px;}

/* ── GANTT PANEL ── */
.g-panel{flex:1;display:flex;flex-direction:column;overflow:hidden;}

/* Phase band header */
.phase-strip{height:28px;min-height:28px;background:rgba(var(--color-card),0.4);border-bottom:1px solid var(--c-border);
  display:flex;align-items:center;padding:0 10px;gap:8px;flex-shrink:0;overflow-x:hidden;}
.ph-pill{display:flex;align-items:center;gap:4px;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;cursor:pointer;border:none;}
.ph-pill .ph-dot{width:7px;height:7px;border-radius:50%;}

/* Stats row */
.g-stats{height:30px;min-height:30px;background:rgba(var(--color-bg),0.3);border-bottom:1px solid var(--c-border);
  display:flex;align-items:center;gap:18px;padding:0 12px;flex-shrink:0;font-size:11px;color:var(--c-muted);}
.stat{display:flex;align-items:center;gap:4px;}
.sdot{width:5px;height:5px;border-radius:50%;}

/* Scroll area */
.g-scroll{flex:1;overflow:auto;position:relative;}
.g-scroll::-webkit-scrollbar{height:5px;width:5px;}
.g-scroll::-webkit-scrollbar-thumb{background:var(--c-border);border-radius:4px;}

/* Left-side artist labels (sticky) */
.g-body{display:flex;position:relative;}
.artist-labels{width:180px;min-width:180px;flex-shrink:0;position:sticky;left:0;z-index:30;background:var(--c-card);border-right:1px solid var(--c-border);}
.al-header{height:var(--hh);border-bottom:1px solid var(--c-border);background:rgba(var(--color-bg),0.8);
  display:flex;align-items:flex-end;padding:0 10px 8px;font-size:10px;font-weight:800;color:var(--c-muted);text-transform:uppercase;letter-spacing:.1em;}
.al-artist{border-bottom:1px solid var(--c-border);background:rgba(var(--color-card),0.5);display:flex;flex-direction:column;justify-content:center;padding:0 10px;}
.al-name{font-size:12px;font-weight:700;color:var(--c-text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.al-cap{font-size:10px;color:var(--c-muted);margin-top:2px;}
.al-load{height:3px;background:var(--c-border);border-radius:2px;margin-top:4px;}
.al-load-bar{height:100%;border-radius:2px;transition:width .3s;}
.al-avatar{width:22px;height:22px;border-radius:50%;font-size:10px;font-weight:800;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-right:6px;}

/* Chart inner */
.chart-wrap{flex:1;position:relative;overflow:visible;}
.chart-hdr{height:var(--hh);position:sticky;top:0;z-index:25;background:rgba(var(--color-bg),0.8);border-bottom:2px solid var(--c-border);display:flex;}
.day-h{width:var(--dw);min-width:var(--dw);height:100%;display:flex;flex-direction:column;
  align-items:center;justify-content:center;border-right:1px solid var(--c-border);
  flex-shrink:0;position:relative;}
.day-h.wknd{background:rgba(0,0,0,0.2);}
.day-h.todayh{background:rgba(var(--color-accent),0.15);}
.dn{font-size:11px;font-weight:600;color:var(--c-muted);}
.dw{font-size:9px;color:var(--c-muted);text-transform:uppercase;}
.todayh .dn,.todayh .dw{color:var(--c-ai);}
.wknd .dn{color:var(--c-muted);opacity:0.7;}
.month-tag{position:absolute;top:5px;left:2px;font-size:9px;font-weight:800;color:var(--c-ai);letter-spacing:.07em;text-transform:uppercase;}

/* Phase overlays on chart */
.phase-overlay{position:absolute;top:0;bottom:0;pointer-events:none;z-index:5;border-left:2px solid;opacity:.25;}

/* Artist bands */
.artist-band{border-bottom:1px solid var(--c-border);position:relative;}
.band-days{display:flex;}
.day-col{width:var(--dw);min-width:var(--dw);flex-shrink:0;border-right:1px solid var(--c-border);opacity:0.5;}
.day-col.wknd{background:rgba(0,0,0,.25);}
.day-col.todayc{background:rgba(var(--color-accent),0.1);border-right:1px dashed rgba(var(--color-accent),0.3);}
.drop-zone{position:absolute;top:0;left:0;right:0;bottom:0;} /* invisible drop target */

/* Task bars */
.tbar{position:absolute;border-radius:5px;cursor:grab;z-index:10;user-select:none;
  display:flex;align-items:center;overflow:hidden;padding:0 7px;
  box-shadow:0 1px 6px rgba(0,0,0,.5);transition:filter .15s,box-shadow .15s;}
.tbar:hover{filter:brightness(1.15);box-shadow:0 3px 16px rgba(0,0,0,.6);z-index:50;}
.tbar:active{cursor:grabbing;}
.tbar.dragging{opacity:.8;z-index:200;transition:none;box-shadow:0 8px 40px rgba(0,0,0,.8);}
.tbar.bar-crit{box-shadow:0 0 0 2px rgb(var(--color-error)),0 3px 14px rgba(var(--color-error),.5)!important;}
.tbar.locked::after{content:'🔒';position:absolute;right:5px;font-size:10px;}
.rh{position:absolute;top:0;bottom:0;width:8px;cursor:ew-resize;z-index:15;}
.rh:hover{background:rgba(255,255,255,.2);}
.rh.l{left:0;border-radius:5px 0 0 5px;}
.rh.r{right:0;border-radius:0 5px 5px 0;}
.bar-label{font-size:11px;font-weight:600;color:rgba(255,255,255,.9);
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;pointer-events:none;flex:1;padding:0 4px;}

/* Today + deadline lines */
.v-line{position:absolute;top:0;width:2px;z-index:28;pointer-events:none;}
.v-line.today{background:var(--c-ai);}
.v-line.deadline{background:rgb(var(--color-error));}
.v-line::after{position:absolute;top:2px;left:3px;font-size:9px;font-weight:800;letter-spacing:.08em;white-space:nowrap;}
.v-line.today::after{content:'TODAY';color:var(--c-ai);}
.v-line.deadline::after{content:'DEADLINE';color:rgb(var(--color-error));}

/* Drop highlight */
.drop-hl{background:rgba(var(--color-accent),0.1);border:1px dashed rgba(var(--color-accent),0.4);}

/* Tooltip */
.g-tip{position:fixed;z-index:600;pointer-events:none;background:var(--c-card);
  border:1px solid var(--c-border);border-radius:9px;padding:12px 16px;min-width:200px;
  font-size:12px;color:var(--c-text);box-shadow:0 10px 40px rgba(0,0,0,.7);
  opacity:0;transition:opacity .1s;}
.g-tip.on{opacity:1;}
.tip-h{font-size:14px;font-weight:700;color:var(--c-ai);margin-bottom:7px;}
.tip-r{display:flex;justify-content:space-between;gap:16px;color:var(--c-muted);margin-top:3px;}
.tip-r span:last-child{color:var(--c-text);font-weight:500;}

/* Phase editor modal */
.modal-bg{position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:400;display:none;align-items:center;justify-content:center;}
.modal-bg.on{display:flex;}
.modal{background:var(--c-card);border:1px solid var(--c-border);border-radius:12px;padding:24px;width:440px;max-width:95vw;}
.modal h2{font-size:16px;font-weight:800;color:var(--c-text);margin-bottom:16px;}
.modal label{font-size:12px;color:var(--c-muted);display:block;margin-bottom:4px;margin-top:10px;}
.modal input,.modal select{width:100%;background:rgba(var(--color-bg),0.8);border:1px solid var(--c-border);color:var(--c-text);
  font-size:13px;padding:7px 10px;border-radius:7px;outline:none;}
.modal input:focus,.modal select:focus{border-color:var(--c-ai);}
.modal-actions{display:flex;gap:8px;margin-top:16px;justify-content:flex-end;}

/* Phase bar (in gantt header) */
.phase-band-bar{position:absolute;top:4px;height:20px;border-radius:4px;display:flex;align-items:center;
  padding:0 8px;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;
  overflow:hidden;white-space:nowrap;opacity:.85;pointer-events:none;}

/* Legend */
.legend{display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.leg{display:flex;align-items:center;gap:4px;font-size:10px;color:var(--c-muted);}
.legbox{width:8px;height:8px;border-radius:2px;}

@keyframes spin{100%{transform:rotate(360deg)}}
.spin{animation:spin 1s linear infinite;display:inline-block;}
</style>

<div class="g-root">
<!-- TOPBAR -->
<div class="g-top">
    <div class="g-logo"><span class="ico material-symbols-outlined" style="font-size:17px">view_timeline</span>Production Scheduler</div>
    <div class="vsep"></div>

    <!-- Project pills (multi-select) -->
    <div class="proj-pills" id="proj-pills">
        <!-- filled by JS -->
    </div>



    <div class="vsep"></div>

    <button class="btn btn-ghost" onclick="openPhaseEditor()">
        <span class="material-symbols-outlined" style="font-size:14px">layers</span> Phases
    </button>

    <div class="spc"></div>

    <div class="legend" id="legend"></div>

    <div class="vsep"></div>

    <label style="display:flex;align-items:center;gap:5px;font-size:10px;color:#64748b;cursor:pointer;white-space:nowrap;">
        <input type="checkbox" id="backwards-chk" style="accent-color:#8b5cf6"> From deadline
    </label>

    <button class="btn btn-ai" id="run-ai">
        <span class="material-symbols-outlined" style="font-size:14px">auto_awesome</span>AI Schedule
    </button>
    <button class="btn btn-green btn-save" id="btn-save">
        <span class="material-symbols-outlined" style="font-size:14px">save</span>Save
    </button>
</div>

<!-- MAIN -->
<div class="g-main">

    <!-- SEARCH DRAWER -->
    <div class="drawer">
        <div class="drawer-search">
            <input type="text" class="search-box" placeholder="Search shots, tasks…" id="search-box" oninput="filterDrawer(this.value)">
        </div>
        <div class="drawer-tabs">
            <button class="dtab on" onclick="drawerTab('unscheduled',this)">Unscheduled</button>
            <button class="dtab" onclick="drawerTab('shots',this)">Shots</button>
            <button class="dtab" onclick="drawerTab('all',this)">All Tasks</button>
        </div>
        <div class="drawer-body" id="drawer-body">
            <div style="text-align:center;color:#4b5563;font-size:12px;padding:20px">Loading…</div>
        </div>
    </div>

    <!-- GANTT PANEL -->
    <div class="g-panel">

        <!-- Phase strip -->
        <div class="phase-strip" id="phase-strip">
            <!-- filled by JS -->
        </div>

        <!-- Stats -->
        <div class="g-stats" id="g-stats">
            <div class="stat"><div class="sdot" style="background:#8b5cf6"></div><span id="st-tasks">0 tasks</span></div>
            <div class="stat"><div class="sdot" style="background:#3b82f6"></div><span id="st-sched">0 scheduled</span></div>
            <div class="stat"><div class="sdot" style="background:#ef4444"></div><span id="st-unsched">0 unscheduled</span></div>
            <div style="margin-left:auto" class="stat">
                <span class="material-symbols-outlined" style="font-size:11px">schedule</span>
                <span id="st-hrs">0h total</span>
            </div>
        </div>

        <!-- Scrollable Gantt -->
        <div class="g-scroll" id="g-scroll">
            <!-- Chart -->
            <div class="chart-wrap" id="chart-wrap">
                <div class="chart-hdr" id="chart-hdr"></div>
                <div id="tracks-wrap"></div>
                <!-- bars injected by JS -->
                <div class="v-line today" id="vl-today"></div>
                <div class="v-line deadline" id="vl-deadline" style="display:none"></div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Tooltip -->
<div class="g-tip" id="g-tip"></div>

<!-- Phase Editor Modal -->
<div class="modal-bg" id="phase-modal">
    <div class="modal">
        <h2><span class="material-symbols-outlined" style="font-size:18px;vertical-align:middle;color:#8b5cf6">layers</span> Manage Phases</h2>
        <div id="phase-list" style="margin-bottom:12px"></div>
        <hr style="border-color:#1f1f2e;margin:12px 0">
        <h3 style="font-size:12px;color:#94a3b8;margin-bottom:8px">Add / Edit Phase</h3>
        <input type="hidden" id="ph-id">
        <label>Project</label>
        <select id="ph-proj"></select>
        <label>Phase Name</label>
        <input type="text" id="ph-name" placeholder="e.g. Pre-Production">
        <label>Color</label>
        <input type="color" id="ph-color" value="#8b5cf6" style="height:34px;padding:2px;">
        <label>Start Date</label>
        <input type="date" id="ph-start">
        <label>End Date</label>
        <input type="date" id="ph-end">
        <div class="modal-actions">
            <button class="btn btn-ghost" onclick="closePhaseModal()">Close</button>
            <button class="btn btn-ai" onclick="savePhase()">Save Phase</button>
        </div>
    </div>
</div>

<script>
// ═══════════════════════════════════════════════════════════════
// CONSTANTS & STATE
// ═══════════════════════════════════════════════════════════════
let DW = 52; const HH = 74, DAYS = 160;
let csrfN = '<?= csrf_token() ?>', csrfH = '<?= csrf_hash() ?>';

// Project color palette
const PROJ_COLORS = ['#3b82f6','#8b5cf6','#f59e0b','#22c55e','#ef4444','#ec4899','#06b6d4','#f97316'];

let state = {
    view: 'artist',           // 'artist' | 'shot'
    projects: [],
    artists: [],
    phases: [],
    tasks: [],
    shots: [],
    selectedProjects: [],     // project ids visible
    projColorMap: {},         // projectId -> color
    drawerTab: 'unscheduled',
    drawerSearch: '',
    pending: {},              // taskId -> update payload
    rowMap: {},               // artistId|shotId -> { height, bandEl, labelEl }
};

// Timeline anchor
const TL = (() => {
    const d = new Date(); d.setDate(d.getDate() - d.getDay() + 1); d.setHours(0,0,0,0); return d;
})();
const today = new Date(); today.setHours(0,0,0,0);

// ═══════════════════════════════════════════════════════════════
// BOOT
// ═══════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', async () => {
    await loadData('all');
    buildHeader();
    renderGantt();
    renderDrawer();
    renderPhaseStrip();
    renderStats();
    buildProjLegend();
    syncScroll();
    initAI();
});

async function loadData(projectIds) {
    const url = `/admin/scheduling/data?project_ids=${projectIds}`;
    const d = await fetch(url).then(r => r.json());
    if (d.status !== 'success') return;

    state.projects = d.projects;
    state.artists  = d.artists;
    state.phases   = d.phases;
    state.tasks    = d.tasks;
    state.shots    = d.shots;

    // Assign colors
    state.projects.forEach((p, i) => { state.projColorMap[p.id] = PROJ_COLORS[i % PROJ_COLORS.length]; });
    state.selectedProjects = state.projects.map(p => p.id);
}

// ═══════════════════════════════════════════════════════════════
// PROJECT PILLS
// ═══════════════════════════════════════════════════════════════
function buildProjLegend() {
    const c = document.getElementById('proj-pills');
    const legend = document.getElementById('legend');
    c.innerHTML = ''; legend.innerHTML = '';

    state.projects.forEach(p => {
        const color = state.projColorMap[p.id];
        // Top pill
        const pill = document.createElement('div');
        pill.className = 'proj-pill on';
        pill.style.cssText = `background:${color}22; color:${color};`;
        pill.dataset.pid = p.id;
        pill.innerHTML = `<div class="proj-dot" style="background:${color}"></div>${p.name}`;
        pill.onclick = () => toggleProject(p.id, pill);
        c.appendChild(pill);
        // Legend
        const leg = document.createElement('div');
        leg.className = 'leg';
        leg.innerHTML = `<div class="legbox" style="background:${color}"></div>${p.name}`;
        legend.appendChild(leg);
    });
}

function toggleProject(id, pill) {
    if (state.selectedProjects.includes(id)) {
        if (state.selectedProjects.length <= 1) return;
        state.selectedProjects = state.selectedProjects.filter(x => x !== id);
        pill.classList.remove('on'); pill.style.borderColor = 'transparent';
    } else {
        state.selectedProjects.push(id);
        pill.classList.add('on');
    }
    renderGantt(); renderDrawer(); renderStats();
}

// ═══════════════════════════════════════════════════════════════
// VIEW TOGGLE
// ═══════════════════════════════════════════════════════════════
function setView(v, btn) {
    state.view = v;
    document.querySelectorAll('.vt-btn').forEach(b => b.classList.remove('on'));
    btn.classList.add('on');
    renderGantt();
}

// ═══════════════════════════════════════════════════════════════
// DATE HEADER
// ═══════════════════════════════════════════════════════════════
function buildHeader() {
    const hdr = document.getElementById('chart-hdr');
    hdr.style.width = (DAYS * DW) + 'px';
    hdr.innerHTML = '';
    let lastM = '';
    for (let i = 0; i < DAYS; i++) {
        const d = new Date(TL); d.setDate(d.getDate() + i);
        const wknd = d.getDay() === 0 || d.getDay() === 6;
        const isT  = d.toDateString() === today.toDateString();
        const m = d.toLocaleDateString('en-US', {month:'short', year:'numeric'});
        const col = document.createElement('div');
        col.className = 'day-h' + (wknd?' wknd':'') + (isT?' todayh':'');
        
        let subGrid = '';
        if (DW >= 100) {
            // If DW > 250, we have ~10px per hour, enough for a 1-2 digit number.
            const step = DW > 250 ? 1 : (DW > 150 ? 3 : 6);
            for (let hr = step; hr < 24; hr += step) {
                const perc = (hr / 24) * 100;
                subGrid += `<div style="position:absolute;left:${perc}%;bottom:0;height:5px;width:1px;background:var(--c-border);opacity:0.5;"></div>`;
                subGrid += `<div style="position:absolute;left:${perc}%;bottom:7px;font-size:8px;color:var(--c-muted);transform:translateX(-50%);">${hr}</div>`;
            }
        }
        
        if (m !== lastM) {
            lastM = m;
            const lbl = document.createElement('span');
            lbl.className = 'month-tag'; lbl.innerText = m.toUpperCase();
            col.appendChild(lbl);
        }
        col.innerHTML += `<span class="dn">${d.getDate()}</span><span class="dw">${d.toLocaleDateString('en-US',{weekday:'narrow'})}</span>${subGrid}`;
        hdr.appendChild(col);
    }
}

// ═══════════════════════════════════════════════════════════════
// GANTT RENDER
// ═══════════════════════════════════════════════════════════════
function renderGantt() {
    const tracksW = document.getElementById('tracks-wrap');
    if (!tracksW) return;
    tracksW.innerHTML = '';
    
    // Remove old bars
    document.querySelectorAll('.tbar').forEach(b => b.remove());
    document.querySelectorAll('.v-line:not(.today):not(.deadline)').forEach(b=>b.remove());

    const chartW = document.getElementById('chart-wrap');
    chartW.style.width = (DAYS * DW) + 'px';

    const filteredTasks = state.tasks.filter(t => state.selectedProjects.includes(t.project_id));

    // Find max gantt_row used
    let maxRow = 0;
    filteredTasks.forEach(t => {
        if (t.start_date && t.end_date && t.gantt_row > maxRow) {
            maxRow = parseInt(t.gantt_row);
        }
    });
    
    // Render Tracks (0 to maxRow + 10)
    const totalTracks = Math.max(15, maxRow + 10);
    const trackEls = [];
    
    for (let i = 0; i <= totalTracks; i++) {
        const track = document.createElement('div');
        track.className = 'track-row';
        track.style.height = '48px';
        track.style.borderBottom = '1px solid rgba(255,255,255,0.03)';
        track.style.position = 'relative';
        track.dataset.row = i;
        track.innerHTML = buildDayCols() + `<div class="drop-zone" data-row="${i}"></div>`;
        tracksW.appendChild(track);
        trackEls.push(track);
        
        // Drop logic
        const dz = track.querySelector('.drop-zone');
        dz.addEventListener('dragover', e => { e.preventDefault(); track.classList.add('drop-hl'); });
        dz.addEventListener('dragleave', e => { track.classList.remove('drop-hl'); });
        dz.addEventListener('drop', e => handleDrawerDrop(e, dz));
    }

    // Place bars
    filteredTasks.forEach(t => {
        if (!t.start_date || !t.end_date) return;
        const rowIndex = parseInt(t.gantt_row) || 0;
        const targetTrack = trackEls[rowIndex] || trackEls[0];
        placeBar(t, targetTrack);
    });

    // Vertical lines
    placeTodayLine(totalTracks);
    placeDeadlineLines();

    // Phase overlays
    renderPhaseOverlays(tracksW, totalTracks);
}

// ═══════════════════════════════════════════════════════════════
// BAR PLACEMENT
// ═══════════════════════════════════════════════════════════════
function placeBar(task, trackEl) {
    if (!task.start_date || !task.end_date) return;

    const s = new Date(task.start_date);
    const e = new Date(task.end_date);
    const off = (s - TL) / 86400000;
    const dur = Math.max(1 / 24, (e - s) / 86400000); // minimum 1 hour visual
    
    if (off >= DAYS || off + dur < 0) return;

    const color    = state.projColorMap[task.project_id] || '#3b82f6';
    const locked   = task.is_undocked == 1;
    const isCrit   = task.is_critical;

    const bar = document.createElement('div');
    bar.className = 'tbar' + (locked?' locked':'') + (isCrit?' bar-crit':'');
    bar.id = 'bar-' + task.id;
    bar.dataset.taskId    = task.id;
    bar.dataset.startStr  = task.start_date;
    bar.dataset.endStr    = task.end_date;
    // 5px top offset to perfectly center 38px bar inside 48px track
    bar.style.cssText = `left:${off*DW}px;width:${dur*DW-2}px;top:5px;height:38px;background:${color};`;

    bar.innerHTML = `
        <div class="rh l"></div>
        <span class="bar-label" id="bl-${task.id}">${dur > 2 ? (task.task_type_name||'')+'·'+(task.sequence_name ? task.sequence_name+'/' : '')+task.shot_number : ''}</span>
        <div class="rh r"></div>`;

    // Dragging
    bindBarDrag(bar, task);
    bindBarTooltip(bar, task);
    bindResizeHandles(bar, task);

    trackEl.appendChild(bar);
}

function buildDayCols() {
    let h = '';
    for (let i=0; i<DAYS; i++) {
        const d = new Date(TL); d.setDate(d.getDate()+i);
        const w = d.getDay()===0||d.getDay()===6;
        const t = d.toDateString()===today.toDateString();
        h += `<div class="day-col${w?' wknd':''}${t?' todayc':''}"></div>`;
    }
    // We can add a pseudo element or background for the sub-grid
    // But since DW is a CSS var, we can just apply a repeating gradient based on var(--dw)
    const subGridCSS = `background: repeating-linear-gradient(to right, transparent, transparent calc(var(--dw)/24 * 6 - 1px), rgba(255,255,255,0.03) calc(var(--dw)/24 * 6 - 1px), rgba(255,255,255,0.03) calc(var(--dw)/24 * 6));`;
    return `<div class="band-days" style="${subGridCSS}">${h}</div>`;
}

// ═══════════════════════════════════════════════════════════════
// VERTICAL LINES
// ═══════════════════════════════════════════════════════════════
function placeTodayLine(rowCount) {
    const off = Math.floor((today - TL) / 86400000);
    const vl  = document.getElementById('vl-today');
    if (off >= 0 && off < DAYS) {
        const h = document.getElementById('tracks-wrap')?.scrollHeight || rowCount * 48;
        vl.style.cssText = `position:absolute;top:${HH}px;left:${off*DW+DW/2}px;height:${h}px;`;
    }
}

function placeDeadlineLines() {
    state.projects.forEach(p => {
        if (!p.deadline || !state.selectedProjects.includes(p.id)) return;
        const d = new Date(p.deadline); d.setHours(0,0,0,0);
        const off = Math.round((d - TL) / 86400000);
        if (off < 0 || off >= DAYS) return;
        const h = document.getElementById('tracks-wrap')?.scrollHeight || 1000;
        const vl = document.getElementById('vl-deadline');
        vl.style.cssText = `position:absolute;top:${HH}px;left:${off*DW}px;height:${h}px;display:block;`;
    });
}

// ═══════════════════════════════════════════════════════════════
// PHASE OVERLAYS + STRIP
// ═══════════════════════════════════════════════════════════════
function renderPhaseStrip() {
    const strip = document.getElementById('phase-strip');
    strip.innerHTML = '';
    const phases = state.phases.filter(ph => state.selectedProjects.includes(ph.project_id));
    if (!phases.length) { strip.style.display = 'none'; return; }
    strip.style.display = 'flex';
    phases.forEach(ph => {
        const pill = document.createElement('button');
        pill.className = 'ph-pill';
        pill.style.background = ph.color + '22'; pill.style.color = ph.color;
        pill.innerHTML = `<span class="ph-dot" style="background:${ph.color}"></span>${ph.name}`;
        if (ph.start_date) pill.title = `${ph.start_date} → ${ph.end_date||'?'}`;
        pill.onclick = () => { /* could open phase edit */ };
        strip.appendChild(pill);
    });
}

function renderPhaseOverlays(tracksW, rowCount) {
    document.querySelectorAll('.phase-overlay').forEach(e=>e.remove());
    const h = document.getElementById('tracks-wrap')?.scrollHeight || rowCount * 48;
    state.phases.filter(ph => state.selectedProjects.includes(ph.project_id) && ph.start_date).forEach(ph => {
        const s   = new Date(ph.start_date); s.setHours(0,0,0,0);
        const e   = ph.end_date ? new Date(ph.end_date) : null;
        const off = Math.round((s - TL) / 86400000);
        const dur = e ? Math.max(1, Math.round((e-s)/86400000)) : DAYS - off;
        if (off >= DAYS || off+dur < 0) return;

        const ov = document.createElement('div');
        ov.className = 'phase-overlay';
        ov.style.cssText = `left:${Math.max(0,off)*DW}px;width:${dur*DW}px;top:${HH}px;height:${h}px;border-color:${ph.color};background:${ph.color}`;
        document.getElementById('chart-wrap').appendChild(ov);
    });
}

// ═══════════════════════════════════════════════════════════════
// STATS
// ═══════════════════════════════════════════════════════════════
function renderStats() {
    const filtered = state.tasks.filter(t => state.selectedProjects.includes(t.project_id));
    const sched = filtered.filter(t => t.start_date && t.end_date).length;
    document.getElementById('st-tasks').innerText   = filtered.length + ' tasks';
    document.getElementById('st-sched').innerText   = sched + ' scheduled';
    document.getElementById('st-unsched').innerText = (filtered.length - sched) + ' unscheduled';
    document.getElementById('st-hrs').innerText     = filtered.reduce((a,t)=>a+(parseFloat(t.estimated_hours)||0),0).toFixed(0)+'h total';
}

// ═══════════════════════════════════════════════════════════════
// DRAWER (search panel)
// ═══════════════════════════════════════════════════════════════
function drawerTab(tab, btn) {
    state.drawerTab = tab;
    document.querySelectorAll('.dtab').forEach(b => b.classList.remove('on'));
    btn.classList.add('on');
    renderDrawer();
}

function filterDrawer(v) {
    state.drawerSearch = v.toLowerCase();
    renderDrawer();
}

function renderDrawer() {
    const body = document.getElementById('drawer-body');
    body.innerHTML = '';
    const filtered = state.tasks.filter(t => state.selectedProjects.includes(t.project_id));
    const search   = state.drawerSearch;

    let items = [];
    if (state.drawerTab === 'unscheduled') {
        items = filtered.filter(t => !t.start_date || !t.end_date);
    } else if (state.drawerTab === 'shots') {
        items = state.shots.filter(s => state.selectedProjects.includes(s.project_id));
        items = items.map(s => ({...s, _isShot: true}));
    } else {
        items = filtered;
    }

    if (search) {
        items = items.filter(i => {
            const text = ((i.shot_number||'')+(i.task_type_name||'')+(i.artist_name||'')+(i.project_name||'')+(i.sequence_name||'')).toLowerCase();
            return text.includes(search);
        });
    }

    if (!items.length) {
        body.innerHTML = '<div style="text-align:center;color:#4b5563;font-size:11px;padding:20px">Nothing found</div>';
        return;
    }

    items.forEach(item => {
        const card = document.createElement('div');
        card.className = 'dcard';
        card.draggable = true;
        const color = state.projColorMap[item.project_id] || '#4b5563';

        const thumb = item.thumbnail_path
            ? `<img class="dcard-thumb" src="/${item.thumbnail_path}" onerror="this.style.display='none'">`
            : `<div class="dcard-thumb-ph">${item._isShot ? '🎬' : '🎯'}</div>`;

        const badge = `<span class="dcard-proj" style="background:${color}22;color:${color}">${item.project_name||'?'}</span>`;

        if (item._isShot) {
            card.innerHTML = `${thumb}<div class="dcard-info">
                <div class="dcard-name">${item.shot_number}</div>
                <div class="dcard-sub">${badge}${item.sequence_name||''}</div>
            </div>`;
            card.dataset.shotId = item.id;
        } else {
            card.innerHTML = `${thumb}<div class="dcard-info">
                <div class="dcard-name">${item.task_type_name||'Task'} · ${item.sequence_name ? item.sequence_name+'/' : ''}${item.shot_number||'?'}</div>
                <div class="dcard-sub">${badge}<span>${item.artist_name||'Unassigned'}</span></div>
                <div class="dcard-sub" style="color:#475569">${item.estimated_hours||0}h · ${item.status}</div>
            </div>`;
            card.dataset.taskId = item.id;
        }

        card.addEventListener('dragstart', e => {
            e.dataTransfer.setData('text/plain', JSON.stringify({
                taskId: item.id, shotId: item.shot_id, projectId: item.project_id
            }));
            card.classList.add('dragging-card');
        });
        card.addEventListener('dragend', () => card.classList.remove('dragging-card'));

        body.appendChild(card);
    });
}

function handleDrawerDrop(e, dz) {
    e.preventDefault();
    const track = dz.closest('.track-row');
    if (track) track.classList.remove('drop-hl');
    
    let data;
    try { data = JSON.parse(e.dataTransfer.getData('text/plain')); } catch { return; }
    if (!data.taskId) return;

    // Calculate drop day
    const chartRect = document.getElementById('chart-wrap').getBoundingClientRect();
    const relX = e.clientX - chartRect.left + document.getElementById('g-scroll').scrollLeft;
    
    // precise hour snapping (or whole days if Ctrl is pressed)
    const snapStep = e.ctrlKey ? DW : (DW / 24);
    const snappedRelX = Math.round(relX / snapStep) * snapStep;
    const offDays = snappedRelX / DW;
    
    const dropDate = new Date(TL.getTime() + offDays * 86400000);
    const task = state.tasks.find(t => t.id == data.taskId);
    if (!task) return;

    const rowIndex = parseInt(dz.dataset.row) || 0;
    task.gantt_row = rowIndex;

    const hrs = task.estimated_hours || 8;
    const days = Math.max(1, Math.ceil(hrs / 8));
    const endDate = new Date(dropDate.getTime() + days * 86400000);

    const pad = n => n.toString().padStart(2,'0');
    const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:00:00`;
    task.start_date = fmt(dropDate);
    task.end_date   = fmt(endDate);
    task.is_undocked = 1;
    
    state.pending[task.id] = {
        id: task.id, 
        start_date: task.start_date, 
        end_date: task.end_date, 
        is_undocked: 1,
        gantt_row: task.gantt_row
    };

    renderGantt();
    renderDrawer();
    renderStats();
    showSaveBtn();
}

// ═══════════════════════════════════════════════════════════════
// BAR DRAG (within gantt)
// ═══════════════════════════════════════════════════════════════
let dragging = null;

function bindBarDrag(bar, task) {
    bar.addEventListener('mousedown', e => {
        if (e.target.classList.contains('rh')) return;
        dragging = {
            bar, 
            task, 
            startX: e.clientX, 
            startY: e.clientY,
            origLeft: parseInt(bar.style.left)||0,
            origTop: parseInt(bar.style.top)||5
        };
        bar.classList.add('dragging');
        e.preventDefault();
    });
}

document.addEventListener('mousemove', e => {
    if (!dragging || dragging.resizing) return;
    const deltaX = e.clientX - dragging.startX;
    const deltaY = e.clientY - dragging.startY;
    
    let nl = dragging.origLeft + deltaX;
    const snapStep = e.ctrlKey ? DW : (DW / 24); // snap to whole day if Ctrl, else hour
    nl = Math.round(nl / snapStep) * snapStep; 
    if(nl < 0) nl = 0;
    
    // allow visual vertical drag
    let nt = dragging.origTop + deltaY;
    
    dragging.bar.style.left = nl + 'px';
    dragging.bar.style.top  = nt + 'px';
});

document.addEventListener('mouseup', e => {
    if (!dragging || dragging.resizing) return;
    dragging.bar.classList.remove('dragging');
    const taskId = dragging.task.id;
    
    const deltaY = e.clientY - dragging.startY;
    const rowDiff = Math.round(deltaY / 48);
    
    let newRow = (parseInt(dragging.task.gantt_row) || 0) + rowDiff;
    if (newRow < 0) newRow = 0;

    const offDays = parseFloat(dragging.bar.style.left) / DW;
    const durDays = parseFloat(dragging.bar.style.width) / DW;
    
    const s = new Date(TL.getTime() + offDays * 86400000);
    const en = new Date(s.getTime() + durDays * 86400000);
    
    const pad = n => n.toString().padStart(2,'0');
    const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:00:00`;

    dragging.task.start_date = fmt(s);
    dragging.task.end_date   = fmt(en);
    dragging.task.gantt_row  = newRow;
    dragging.task.is_undocked = 1;
    
    state.pending[taskId] = {
        id: taskId, 
        start_date: fmt(s), 
        end_date: fmt(en), 
        is_undocked: 1, 
        gantt_row: newRow
    };
    
    showSaveBtn();
    renderGantt();
    dragging = null;
});

// ═══════════════════════════════════════════════════════════════
// RESIZE HANDLES
// ═══════════════════════════════════════════════════════════════
function bindResizeHandles(bar, task) {
    const lh = bar.querySelector('.rh.l');
    const rh = bar.querySelector('.rh.r');

    // RIGHT resize (extend end date)
    rh.addEventListener('mousedown', e => {
        e.stopPropagation();
        const startX   = e.clientX;
        const startW   = parseInt(bar.style.width)||DW;
        const startLeft= parseInt(bar.style.left)||0;

        const onMove = ev => {
            const delta = ev.clientX - startX;
            let nw = startW + delta;
            const snapStep = ev.ctrlKey ? DW : (DW / 24);
            nw = Math.round(nw / snapStep) * snapStep;
            if (nw < snapStep) nw = snapStep;
            bar.style.width = nw + 'px';
        };
        const onUp = () => {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            const offDays = parseFloat(bar.style.left) / DW;
            const durDays = parseFloat(bar.style.width) / DW;
            const s = new Date(TL.getTime() + offDays * 86400000);
            const en = new Date(s.getTime() + durDays * 86400000);
            const pad = n => n.toString().padStart(2,'0');
            const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:00:00`;
            
            task.end_date = fmt(en); task.is_undocked = 1;
            state.pending[task.id] = {id:task.id, start_date:task.start_date||fmt(s), end_date:fmt(en), is_undocked:1};
            const lbl = document.getElementById('bl-'+task.id);
            if(lbl && durDays >= 1) lbl.innerText = (task.task_type_name||'')+'·'+(task.sequence_name ? task.sequence_name+'/' : '')+(task.shot_number||'');
            showSaveBtn();
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    });

    // LEFT resize (change start date)
    lh.addEventListener('mousedown', e => {
        e.stopPropagation();
        const startX    = e.clientX;
        const startLeft = parseInt(bar.style.left)||0;
        const startW    = parseInt(bar.style.width)||DW;

        const onMove = ev => {
            const delta = ev.clientX - startX;
            let nl = startLeft + delta;
            const snapStep = ev.ctrlKey ? DW : (DW / 24);
            nl = Math.round(nl / snapStep) * snapStep; 
            if(nl < 0) nl = 0;
            const nw = startW - (nl - startLeft);
            if (nw < snapStep) return;
            bar.style.left  = nl + 'px';
            bar.style.width = nw + 'px';
        };
        const onUp = () => {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            const offDays = parseFloat(bar.style.left) / DW;
            const durDays = parseFloat(bar.style.width) / DW;
            const s = new Date(TL.getTime() + offDays * 86400000);
            const en = new Date(s.getTime() + durDays * 86400000);
            const pad = n => n.toString().padStart(2,'0');
            const fmt = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())} ${pad(d.getHours())}:00:00`;
            
            task.start_date = fmt(s); task.is_undocked = 1;
            state.pending[task.id] = {id:task.id, start_date:fmt(s), end_date:task.end_date||fmt(en), is_undocked:1};
            showSaveBtn();
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    });
}

// ═══════════════════════════════════════════════════════════════
// TOOLTIP
// ═══════════════════════════════════════════════════════════════
const tip = document.getElementById('g-tip');
function bindBarTooltip(bar, task) {
    bar.addEventListener('mouseenter', e => {
        const color = state.projColorMap[task.project_id]||'#4b5563';
        tip.innerHTML = `
            <div class="tip-h" style="color:${color}">${task.task_type_name||'Task'} · ${task.sequence_name ? task.sequence_name+'/' : ''}${task.shot_number||'?'}</div>
            <div class="tip-r"><span>Project</span><span>${task.project_name||'?'}</span></div>
            <div class="tip-r"><span>Artist</span><span>${task.artist_name||'Unassigned'}</span></div>
            <div class="tip-r"><span>Phase</span><span>${task.phase_name||'—'}</span></div>
            <div class="tip-r"><span>Hours</span><span>${task.estimated_hours||0}h</span></div>
            <div class="tip-r"><span>Status</span><span>${task.status||'?'}</span></div>
            <div class="tip-r"><span>Start</span><span>${task.start_date?.split(' ')[0]||'—'}</span></div>
            <div class="tip-r"><span>End</span><span>${task.end_date?.split(' ')[0]||'—'}</span></div>
            <div class="tip-r"><span>Mode</span><span>${task.is_undocked==1?'🔒 Locked':'🤖 Auto'}</span></div>`;
        tip.classList.add('on');
    });
    bar.addEventListener('mousemove', e => { tip.style.left=(e.clientX+14)+'px'; tip.style.top=(e.clientY-10)+'px'; });
    bar.addEventListener('mouseleave', () => tip.classList.remove('on'));
}

// ═══════════════════════════════════════════════════════════════
// AI SCHEDULE
// ═══════════════════════════════════════════════════════════════
function initAI() {
    document.getElementById('run-ai').addEventListener('click', () => {
        if (!state.selectedProjects.length) return alert('Select at least one project');
        const btn = document.getElementById('run-ai');
        btn.classList.add('busy');
        btn.innerHTML = '<span class="spin material-symbols-outlined" style="font-size:14px">sync</span>Calculating…';
        const backwards = document.getElementById('backwards-chk').checked ? 1 : 0;
        const pid = state.selectedProjects[0]; // Schedule first selected project

        fetch('/admin/scheduling/autoSchedule', {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:`project_id=${pid}&backwards=${backwards}&${csrfN}=${csrfH}`
        }).then(r=>r.json()).then(d => {
            btn.classList.remove('busy');
            btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:14px">auto_awesome</span>AI Schedule';
            if (d.status !== 'success') return alert(d.message||'Error');
            d.preview_tasks.forEach(pt => {
                const t = state.tasks.find(x => x.id == pt.id);
                if (!t || t.is_undocked == 1) return;
                t.start_date = pt.start_date; t.end_date = pt.end_date;
                t.is_critical = pt.is_critical;
                state.pending[t.id] = {id:t.id, start_date:pt.start_date, end_date:pt.end_date, is_undocked:0};
            });
            renderGantt(); renderDrawer(); renderStats(); showSaveBtn();
        }).catch(() => {
            btn.classList.remove('busy');
            btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:14px">auto_awesome</span>AI Schedule';
            alert('Network error');
        });
    });
}

// ═══════════════════════════════════════════════════════════════
// SAVE
// ═══════════════════════════════════════════════════════════════
function showSaveBtn() {
    document.getElementById('btn-save').classList.add('show');
}
document.getElementById('btn-save').addEventListener('click', () => {
    const updates = Object.values(state.pending);
    if (!updates.length) return;
    const btn = document.getElementById('btn-save');
    btn.innerHTML = 'Saving…'; btn.style.opacity = '.7';
    const fd = new URLSearchParams();
    fd.append('updates', JSON.stringify(updates)); fd.append(csrfN, csrfH);
    fetch('/admin/scheduling/saveDates', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:fd})
    .then(r=>r.json()).then(d => {
        btn.style.opacity = '1';
        if (d.status==='success') {
            btn.innerHTML = '<span class="material-symbols-outlined" style="font-size:14px">check_circle</span>Saved!';
            btn.style.background='#166534';
            setTimeout(()=>{ btn.classList.remove('show'); btn.innerHTML='<span class="material-symbols-outlined" style="font-size:14px">save</span>Save'; btn.style.background=''; state.pending={}; }, 2200);
        } else alert(d.message||'Save failed');
    });
});

// ═══════════════════════════════════════════════════════════════
// PHASE MODAL
// ═══════════════════════════════════════════════════════════════
function openPhaseEditor() {
    const modal = document.getElementById('phase-modal');
    modal.classList.add('on');
    // Populate project dropdown
    const sel = document.getElementById('ph-proj');
    sel.innerHTML = state.projects.map(p=>`<option value="${p.id}">${p.name}</option>`).join('');
    renderPhaseList();
}
function closePhaseModal() { document.getElementById('phase-modal').classList.remove('on'); }
function renderPhaseList() {
    const list = document.getElementById('phase-list');
    const phases = state.phases.filter(ph => state.selectedProjects.includes(ph.project_id));
    list.innerHTML = phases.map(ph=>`
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;padding:6px 8px;background:#0f0f1a;border-radius:6px;">
            <div style="width:12px;height:12px;border-radius:3px;background:${ph.color};flex-shrink:0;"></div>
            <span style="flex:1;font-size:12px;color:#e2e8f0">${ph.name}</span>
            <span style="font-size:10px;color:#4b5563">${ph.start_date||'no date'} → ${ph.end_date||'?'}</span>
            <button onclick="editPhase(${ph.id})" style="background:none;border:none;color:#8b5cf6;cursor:pointer;font-size:11px">Edit</button>
            <button onclick="deletePhase(${ph.id})" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:13px">×</button>
        </div>`).join('') || '<div style="font-size:11px;color:#4b5563">No phases yet.</div>';
}
function savePhase() {
    const fd = new URLSearchParams();
    fd.append('id',         document.getElementById('ph-id').value);
    fd.append('project_id', document.getElementById('ph-proj').value);
    fd.append('name',       document.getElementById('ph-name').value);
    fd.append('color',      document.getElementById('ph-color').value);
    fd.append('start_date', document.getElementById('ph-start').value);
    fd.append('end_date',   document.getElementById('ph-end').value);
    fd.append(csrfN, csrfH);
    fetch('/admin/phases/save', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:fd})
    .then(r=>r.json()).then(d => {
        if (d.status==='success') { location.reload(); }
        else alert(d.message);
    });
}
function deletePhase(id) {
    if (!confirm('Delete this phase?')) return;
    const fd = new URLSearchParams(); fd.append('id',id); fd.append(csrfN,csrfH);
    fetch('/admin/phases/delete', {method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:fd})
    .then(r=>r.json()).then(d=>{ if(d.status==='success') location.reload(); });
}
function editPhase(id) {
    const ph = state.phases.find(p=>p.id==id);
    if (!ph) return;
    document.getElementById('ph-id').value    = ph.id;
    document.getElementById('ph-proj').value  = ph.project_id;
    document.getElementById('ph-name').value  = ph.name;
    document.getElementById('ph-color').value = ph.color;
    document.getElementById('ph-start').value = ph.start_date||'';
    document.getElementById('ph-end').value   = ph.end_date||'';
}

// ═══════════════════════════════════════════════════════════════
// SCROLL SYNC
// ═══════════════════════════════════════════════════════════════
function syncScroll() {
    const scroll = document.getElementById('g-scroll');
    scroll.addEventListener('scroll', () => {
        document.getElementById('artist-labels').scrollTop = scroll.scrollTop;
    });
}

// ═══════════════════════════════════════════════════════════════
// ZOOMING
// ═══════════════════════════════════════════════════════════════
function updateZoom(newDW) {
    DW = Math.max(20, Math.min(newDW, 400));
    document.documentElement.style.setProperty('--dw', DW + 'px');
    
    // Update chart total width
    document.getElementById('chart-wrap').style.width = (DAYS * DW) + 'px';
    document.getElementById('chart-hdr').style.width = (DAYS * DW) + 'px';

    // Update bars
    document.querySelectorAll('.tbar').forEach(bar => {
        const off = Math.round((new Date(bar.dataset.startStr) - TL) / 86400000 * 24) / 24; // hour precision
        const s = new Date(bar.dataset.startStr);
        const e = new Date(bar.dataset.endStr);
        const dur = (e - s) / 86400000;
        bar.style.left = (off * DW) + 'px';
        bar.style.width = (dur * DW - 2) + 'px';
    });

    // Update phase overlays
    document.querySelectorAll('.phase-overlay').forEach(ov => {
        const phId = ov.dataset.phId;
        const ph = state.phases.find(p => p.id == phId);
        if(!ph) return;
        const s = new Date(ph.start_date); s.setHours(0,0,0,0);
        const e = ph.end_date ? new Date(ph.end_date) : null;
        const off = Math.round((s - TL) / 86400000);
        const dur = e ? Math.max(1, Math.round((e-s)/86400000)) : DAYS - off;
        ov.style.left = Math.max(0, off) * DW + 'px';
        ov.style.width = (dur * DW) + 'px';
    });

    // Update today / deadline lines
    const tOff = Math.floor((today - TL) / 86400000);
    const vlT = document.getElementById('vl-today');
    if(vlT) vlT.style.left = (tOff * DW + DW/2) + 'px';

    document.querySelectorAll('.v-line.deadline').forEach(vl => {
        const pid = vl.dataset.pid;
        const p = state.projects.find(x => x.id == pid);
        if(!p || !p.deadline) return;
        const d = new Date(p.deadline); d.setHours(0,0,0,0);
        const off = Math.round((d - TL) / 86400000);
        vl.style.left = (off * DW) + 'px';
    });
    
    // Rebuild header for hourly marks if DW is large
    buildHeader();
}

// Bind Wheel Event
document.getElementById('g-scroll').addEventListener('wheel', e => {
    if (e.ctrlKey) {
        e.preventDefault();
        const delta = e.deltaY > 0 ? -0.2 : 0.2; // 20% zoom steps
        let newDW = DW * (1 + delta);
        if (newDW > 400) newDW = 400;
        if (newDW < 20) newDW = 20;
        
        // Calculate the mouse position relative to scroll to zoom into the cursor
        const scrollEl = document.getElementById('g-scroll');
        const rect = scrollEl.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const scrollX = scrollEl.scrollLeft;
        const totalX = scrollX + mouseX;
        const ratio = totalX / DW;
        
        updateZoom(newDW);
        
        // Adjust scroll position to keep cursor fixed
        scrollEl.scrollLeft = (ratio * DW) - mouseX;
    }
}, {passive: false});

// Close modal on bg click
document.getElementById('phase-modal').addEventListener('click', function(e) {
    if (e.target === this) closePhaseModal();
});
</script>
<?= $this->endSection() ?>
