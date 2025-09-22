// public/js/app.js
// API base is relative because index.html is in public/ and api/ is one level up.
const API_BASE = '../api';

async function apiGet(path){
  const res = await fetch(API_BASE + '/' + path);
  return res.json();
}
async function apiPost(path, body){
  const res = await fetch(API_BASE + '/' + path, {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify(body)
  });
  return res.json();
}

/* Auth handlers */
let currentUser = null;

async function login(username, password){
  const res = await apiPost('login.php', {username, password});
  if(res.success){
    currentUser = res.user;
    onLogin();
  } else {
    alert('Login mislukt: ' + (res.message || ''));
  }
}

async function logout(){
  await apiPost('logout.php', {});
  currentUser = null;
  onLogout();
}

function onLogin(){
  document.getElementById('login-box').style.display = 'none';
  document.getElementById('logged-box').style.display = 'block';
  document.getElementById('btn-logout').style.display = 'inline-block';
  document.getElementById('logged-name').textContent = currentUser.naam;
  document.getElementById('logged-role').textContent = currentUser.rol;
}
function onLogout(){
  document.getElementById('login-box').style.display = 'block';
  document.getElementById('logged-box').style.display = 'none';
  document.getElementById('btn-logout').style.display = 'none';
  document.getElementById('logged-name').textContent = '';
  document.getElementById('logged-role').textContent = '';
}

/* UI actions */
document.getElementById('btn-login').addEventListener('click', ()=> {
  const u = document.getElementById('username').value.trim();
  const p = document.getElementById('password').value;
  if(!u||!p) return alert('Vul gebruikersnaam en wachtwoord in');
  login(u,p).then(loadAll);
});
document.getElementById('btn-logout').addEventListener('click', ()=> logout());
document.getElementById('btn-refresh').addEventListener('click', ()=> loadAll());
document.getElementById('btn-filter').addEventListener('click', ()=> {
  const s = document.getElementById('filter-status').value;
  loadTaken(s);
});

document.getElementById('btn-create-task').addEventListener('click', async ()=>{
  const attractie_id = document.getElementById('new-task-attractie').value;
  const datum = document.getElementById('new-task-datum').value;
  const personeel_id = document.getElementById('new-task-personeel').value || null;
  const opmerkingen = document.getElementById('new-task-opmerking').value || null;
  if(!attractie_id || !datum) return alert('Kies attractie en datum');
  const res = await apiPost('taken.php?action=create', {attractie_id, datum, personeel_id, opmerkingen});
  if(res.success){ alert('Taak aangemaakt'); loadTaken(); } else alert('Fout: '+(res.message||''));
});

/* load lists */
async function loadAttracties(){
  const res = await apiGet('attracties.php?action=list');
  const tbody = document.querySelector('#table-attracties tbody');
  tbody.innerHTML = '';
  const sel = document.getElementById('new-task-attractie');
  sel.innerHTML = '<option value="">-- kies attractie --</option>';
  if(!res.success) return;
  res.data.forEach(a=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${escapeHtml(a.naam)}</td><td>${escapeHtml(a.locatie||'')}</td>
      <td>${escapeHtml(a.type||'')}</td><td>${escapeHtml(a.specificaties||'')}</td>
      <td>
        <button class="btn small" onclick="viewAttractie(${a.id})">Bekijk</button>
        <button class="btn ghost small" onclick="deleteAttractie(${a.id})">Verwijder</button>
      </td>`;
    tbody.appendChild(tr);
    const opt = document.createElement('option'); opt.value = a.id; opt.textContent = a.naam;
    sel.appendChild(opt);
  });
}

async function loadPersoneel(){
  const res = await apiGet('personeel.php?action=list');
  const sel = document.getElementById('new-task-personeel');
  sel.innerHTML = '<option value="">-- geen voorkeur --</option>';
  if(!res.success) return;
  res.data.forEach(p=>{
    const opt = document.createElement('option'); opt.value = p.id; opt.textContent = `${p.naam} (${p.rol})`;
    sel.appendChild(opt);
  });
}

async function loadTaken(status=''){
  const url = 'taken.php?action=list' + (status ? '&status=' + encodeURIComponent(status) : '');
  const res = await apiGet(url);
  const tbody = document.querySelector('#table-taken tbody');
  tbody.innerHTML = '';
  if(!res.success) return;
  res.data.forEach(t=>{
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${escapeHtml(t.attractie_naam || '—')}</td>
      <td>${escapeHtml(t.datum)}</td>
      <td><span class="badge ${badgeClass(t.status)}">${t.status}</span></td>
      <td>${escapeHtml(t.personeel_naam || '')}</td>
      <td>
        ${t.status !== 'voltooid' ? `<button class="btn small" onclick="updateStatus(${t.id},'in_behandeling')">Start</button>
        <button class="btn small" onclick="updateStatus(${t.id},'voltooid')">Voltooi</button>` : ''}
        <button class="btn ghost small" onclick="viewTask(${t.id})">Details</button>
      </td>`;
    tbody.appendChild(tr);
  });
}

/* helpers used in inline handlers (exposed) */
window.viewAttractie = async (id) => {
  const res = await apiGet('attracties.php?action=get&id=' + id);
  if(!res.success) return alert('Fout ophalen attractie');
  const a = res.data;
  alert(`Attractie: ${a.naam}\nLocatie: ${a.locatie}\nType: ${a.type}\nSpecificaties: ${a.specificaties||''}`);
};

window.deleteAttractie = async (id) => {
  if(!confirm('Verwijder attractie?')) return;
  const res = await apiPost('attracties.php?action=delete', {id});
  if(res.success) loadAttracties();
  else alert('Fout: ' + (res.message || ''));
};

window.viewTask = async (id) => {
  const res = await apiGet('taken.php?action=get&id=' + id);
  if(!res.success) return alert('Fout ophalen taak');
  const t = res.data;
  alert(`Taak #${t.id}\nAttractie: ${t.attractie_naam}\nDatum: ${t.datum}\nStatus: ${t.status}\nOpmerking: ${t.opmerkingen||''}`);
};

window.updateStatus = async (id, status) => {
  const res = await apiPost('taken.php?action=update', {id, status});
  if(res.success) loadTaken(document.getElementById('filter-status').value);
  else alert('Fout: '+(res.message||''));
};

/* small utils */
function badgeClass(status){
  if(status === 'voltooid') return 'done';
  if(status === 'in_behandeling') return 'progress';
  return 'open';
}
function escapeHtml(s){
  if(!s) return '';
  return String(s).replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));
}

async function loadAll(){
  await loadAttracties();
  await loadPersoneel();
  await loadTaken();
}

/* initial load */
loadAll();
