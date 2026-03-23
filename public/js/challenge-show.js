// ============================================================
// Challenge Show — Lógica principal de la pizarra
// ============================================================

// --- Estado global (inicializado desde el servidor) ---
let seconds    = window.ChallengeConfig.seconds;
let isRunning  = window.ChallengeConfig.isRunning;
let wasRunning = window.ChallengeConfig.isRunning;

const { challengeId, isDocente, userId, minPoints, maxPoints } = window.ChallengeConfig;

const timerElement = document.getElementById('timer');

let currentParticipantPosition = 0;
let totalParticipants          = 0;
let suggestedPoints            = 0;

// ============================================================
// Utilidades
// ============================================================

function formatTime(totalSeconds) {
    const h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
    const m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
    const s = (totalSeconds % 60).toString().padStart(2, '0');
    return `${h}:${m}:${s}`;
}

function calculateSuggestedPoints(position, total) {
    if (total <= 1) return maxPoints;
    const range  = maxPoints - minPoints;
    const factor = (total - position) / (total - 1);
    return Math.round(minPoints + (range * factor));
}

// Petición AJAX genérica (POST con CSRF)
function ajaxPost(url, body, onSuccess, onError) {
    const formData = body instanceof FormData ? body : (() => {
        const fd = new FormData();
        Object.entries(body).forEach(([k, v]) => fd.append(k, v));
        return fd;
    })();

    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch(url, {
        method:  'POST',
        body:    formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept':           'application/json',
            'X-CSRF-TOKEN':     document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
    })
        .then(res => res.json().then(data => ({ ok: res.ok, status: res.status, data })))
        .then(({ ok, status, data }) => {
            if (ok && data.success) {
                onSuccess(data);
            } else {
                let msg = 'Error al guardar. Intenta de nuevo.';
                if (data.message)      msg = data.message;
                else if (data.errors)  msg = Object.values(data.errors).flat()[0];
                onError ? onError(msg, status) : alert(`Error (${status}): ${msg}`);
            }
        })
        .catch(err => {
            console.error('AJAX error:', err);
            alert('Error de red. Verifica tu conexión.');
        });
}

// ============================================================
// Cronómetro global
// ============================================================

setInterval(() => {
    if (isRunning) {
        seconds++;
        if (timerElement) timerElement.textContent = formatTime(seconds);
    }

    // Detectar transición running → parado (desafío finalizado en otro cliente)
    if (wasRunning && !isRunning) {
        handleChallengeFinalized();
    }
    wasRunning = isRunning;
}, 1000);

/**
 * Se dispara UNA vez cuando el desafío pasa de activo a finalizado.
 * Inyecta tiempo estimado en tarjetas sin tiempo registrado.
 */
function handleChallengeFinalized() {
    if (!document.getElementById('finalizedBanner')) {
        const banner = document.createElement('div');
        banner.id = 'finalizedBanner';
        Object.assign(banner.style, {
            position:     'fixed',
            top:          '1rem',
            left:         '50%',
            transform:    'translateX(-50%)',
            zIndex:       '9999',
            background:   'linear-gradient(135deg,#1e293b,#334155)',
            color:        'white',
            padding:      '0.75rem 1.5rem',
            borderRadius: '10px',
            fontWeight:   '700',
            boxShadow:    '0 4px 20px rgba(0,0,0,0.3)',
            fontSize:     '1rem',
        });
        banner.textContent = '⏹️ Desafío finalizado — ' + formatTime(seconds);
        document.body.appendChild(banner);
        setTimeout(() => banner.remove(), 6000);
    }

    // Mostrar tiempo estimado en tarjetas que aún no tienen tiempo registrado
    document.querySelectorAll('.participant-card:not(.placeholder)').forEach(card => {
        const infoDiv = card.querySelector('.student-info');
        if (!infoDiv) return;
        if (!infoDiv.querySelector('.submission-time')) {
            const timeDiv = document.createElement('div');
            timeDiv.className = 'submission-time submission-time-estimated';
            timeDiv.title     = 'Tiempo estimado al finalizar el desafío';
            timeDiv.textContent = `⏱ ~${formatTime(seconds)}`;
            infoDiv.appendChild(timeDiv);
        }
    });
}

// ============================================================
// Polling — actualización cada 3 s
// ============================================================

function fetchData() {
    fetch(`/challenge/${challengeId}/data`)
        .then(res => res.json())
        .then(data => {
            seconds   = data.challenge.current_time_seconds;
            isRunning = data.challenge.is_running;
            if (timerElement) timerElement.textContent = formatTime(seconds);
            updateParticipantsGrid(data.participants);
        })
        .catch(() => {}); // silenciar errores de red temporales
}

setInterval(fetchData, 3000);

// ============================================================
// Actualización de tarjetas de participantes (AJAX polling)
// ============================================================

function updateParticipantsGrid(participants) {
    // No actualizar mientras hay un modal abierto (evita race conditions)
    const openModals = ['scoreModal', 'deliveryModal'];
    if (openModals.some(id => {
        const el = document.getElementById(id);
        return el && el.style.display === 'flex';
    })) return;

    participants.forEach((p, index) => {
        const card = document.getElementById(`participant-card-${p.id}`);

        if (!card) {
            // Nuevo participante detectado → recargar página
            const domCount = document.querySelectorAll('.participant-card:not(.placeholder)').length;
            if (participants.length !== domCount) {
                location.reload();
            }
            return;
        }

        // --- Rango y posición ---
        let classes = `participant-card ${index < 3 ? 'top-rank rank-' + (index + 1) : ''}`;
        if (p.user_id === userId) classes += ' current-user';
        card.className = classes;

        const badge = card.querySelector('.position-badge');
        if (badge) {
            if      (index === 0) badge.innerHTML = '<span class="medal">🥇</span>';
            else if (index === 1) badge.innerHTML = '<span class="medal">🥈</span>';
            else if (index === 2) badge.innerHTML = '<span class="medal">🥉</span>';
            else                  badge.innerHTML = `<span class="rank-number">#${index + 1}</span>`;
        }

        // --- Puntos ---
        const pointsEl = card.querySelector('.points-value');
        if (pointsEl) pointsEl.textContent = p.points;

        // --- Botones del docente ---
        if (isDocente) {
            // Botón ⚙️ ajustar
            const settingsBtn = card.querySelector('.btn-icon.settings');
            if (settingsBtn) {
                settingsBtn.setAttribute('onclick',
                    `openScoreModal(${p.id}, '${escapeForJs(p.name)}', ${p.points}, ${p.duration_seconds || 0}, ${p.finished_at ? 'true' : 'false'})`
                );
            }

            // Botón entregar/devolver — crearlo si no existe
            const actionsDiv = card.querySelector('.card-actions');
            if (actionsDiv) {
                let deliveryBtn = card.querySelector('.btn-icon.validate');
                if (!deliveryBtn) {
                    deliveryBtn = document.createElement('button');
                    deliveryBtn.type      = 'button';
                    deliveryBtn.className = 'btn-icon validate';
                    actionsDiv.appendChild(deliveryBtn);
                }

                if (p.finished_at) {
                    deliveryBtn.innerHTML = '🔄';
                    deliveryBtn.title     = 'Devolver Trabajo';
                    deliveryBtn.classList.add('validated');
                    deliveryBtn.setAttribute('onclick', `openDeliveryModal(${p.id}, '${escapeForJs(p.name)}', true)`);
                } else {
                    deliveryBtn.innerHTML = '⏰';
                    deliveryBtn.title     = 'Entregar Trabajo';
                    deliveryBtn.classList.remove('validated');
                    deliveryBtn.setAttribute('onclick', `openDeliveryModal(${p.id}, '${escapeForJs(p.name)}', false)`);
                }
            }
        }

        // --- Tiempo de entrega ---
        const infoDiv = card.querySelector('.student-info');
        if (infoDiv) {
            let timeDiv = infoDiv.querySelector('.submission-time');

            if (p.finished_at && p.formatted_time) {
                // Estudiante entregó: mostrar o actualizar tiempo
                if (!timeDiv) {
                    timeDiv = document.createElement('div');
                    timeDiv.className = 'submission-time';
                    infoDiv.appendChild(timeDiv);
                }
                // Quitar clase de estimado si la tenía
                timeDiv.classList.remove('submission-time-estimated');
                timeDiv.textContent = `⏱ ${p.formatted_time}`;
            } else if (!p.finished_at && timeDiv) {
                // Trabajo devuelto: eliminar el tiempo de la tarjeta
                timeDiv.remove();
            }
        }
    });
}

/** Escapa comillas simples en nombres para uso seguro en atributos onclick */
function escapeForJs(str) {
    return String(str).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

// ============================================================
// Modal de ajuste de puntos/tiempo (⚙️ ScoreModal)
// ============================================================

const scoreModal  = document.getElementById('scoreModal');
const modalName   = document.getElementById('modalStudentName');
const modalInput  = document.getElementById('modalPointsInput');
const modalForm   = document.getElementById('scoreForm');

// Validación en tiempo real del input de puntos
if (modalInput) {
    modalInput.addEventListener('input', function () {
        let val = parseInt(this.value);
        if (isNaN(val) || val < 0)         this.value = 0;
        if (val > maxPoints)               this.value = maxPoints;
    });
}

function openScoreModal(participantId, name, points, durationSeconds, hasFinished) {
    modalName.textContent  = '⚙️ Ajustar: ' + name;
    modalInput.value       = points;

    // Usar setAttribute en lugar de .action = para evitar colisiones DOM
    modalForm.setAttribute('action', `/challenge/${challengeId}/participant/${participantId}/score`);

    const deleteForm = document.getElementById('deleteParticipantForm');
    if (deleteForm) {
        deleteForm.setAttribute('action', `/challenge/${challengeId}/participant/${participantId}/delete`);
    }

    // Sección de tiempo — siempre visible
    const timeSection     = document.getElementById('timeAdjustSection');
    const timeSectionTitle = document.getElementById('timeSectionTitle');
    const modalMinutes    = document.getElementById('modalMinutes');
    const modalSeconds    = document.getElementById('modalSeconds');

    timeSection.style.display = 'block';

    if (hasFinished && durationSeconds > 0) {
        modalMinutes.value = Math.floor(durationSeconds / 60);
        modalSeconds.value = durationSeconds % 60;
        if (timeSectionTitle) timeSectionTitle.textContent = '⏱️ Tiempo Final';
    } else {
        // Pre-cargar con el tiempo actual del cronómetro global
        modalMinutes.value = Math.floor(seconds / 60);
        modalSeconds.value = seconds % 60;
        if (timeSectionTitle) timeSectionTitle.textContent = '⏱️ Tiempo de Actividad';
    }

    // Calcular posición actual y puntos sugeridos
    const cards = document.querySelectorAll('.participant-card:not(.placeholder)');
    totalParticipants = cards.length;
    currentParticipantPosition = 1;
    cards.forEach((card, index) => {
        if (card.id === `participant-card-${participantId}`) {
            currentParticipantPosition = index + 1;
        }
    });

    suggestedPoints = calculateSuggestedPoints(currentParticipantPosition, totalParticipants);
    const suggestedEl = document.getElementById('suggestedPointsValue');
    const suggestedInfo = document.getElementById('suggestedPointsInfo');
    if (suggestedEl)   suggestedEl.textContent  = suggestedPoints;
    if (suggestedInfo) suggestedInfo.style.display = 'block';

    scoreModal.style.display = 'flex';
}

function closeScoreModal() {
    if (scoreModal) scoreModal.style.display = 'none';
    const info = document.getElementById('suggestedPointsInfo');
    if (info) info.style.display = 'none';
}

function adjustModalScore(amount) {
    let newVal = (parseInt(modalInput.value) || 0) + amount;
    newVal = Math.max(0, Math.min(maxPoints, newVal));
    modalInput.value = newVal;
}

function applySuggestedPoints() {
    modalInput.value = suggestedPoints;
}

// Ajustar tiempo: positivo = penalización, negativo = reducción
function adjustModalTime(deltaSecs) {
    const minsEl = document.getElementById('modalMinutes');
    const secsEl = document.getElementById('modalSeconds');
    let total = ((parseInt(minsEl.value) || 0) * 60) + (parseInt(secsEl.value) || 0) + deltaSecs;
    if (total < 0) total = 0;
    minsEl.value = Math.floor(total / 60);
    secsEl.value = total % 60;
}

// Aliases para compatibilidad con botones del HTML
function setPenalty(secs)         { adjustModalTime(secs);  }
function addModalPenalty(secs)    { adjustModalTime(secs);  }
function subtractModalTime(secs)  { adjustModalTime(-secs); }

// ============================================================
// Modal de entrega/devolución de trabajo (⏰ DeliveryModal)
// ============================================================

const deliveryModal     = document.getElementById('deliveryModal');
const deliveryModalName = document.getElementById('deliveryModalStudentName');
const deliveryForm      = document.getElementById('deliveryForm');
const deliveryAction    = document.getElementById('deliveryAction');
const btnSubmitWork     = document.getElementById('btnSubmitWork');
const btnReturnWork     = document.getElementById('btnReturnWork');
const deliveryMessage   = document.getElementById('deliveryMessage');

function openDeliveryModal(participantId, name, hasFinished) {
    // Usar setAttribute para evitar colisión con inputs hijos
    deliveryForm.setAttribute('action', `/challenge/${challengeId}/participant/${participantId}/validate`);

    deliveryModalName.textContent = hasFinished
        ? `🔄 Devolver Trabajo: ${name}`
        : `⏰ Entregar Trabajo: ${name}`;

    if (hasFinished) {
        deliveryMessage.innerHTML =
            '🔄 Este estudiante ya <strong>entregó su trabajo</strong>.<br>' +
            '¿Deseas <strong>devolverlo</strong> para que continúe trabajando?';
        btnSubmitWork.style.display = 'none';
        btnReturnWork.style.display = 'inline-block';
        deliveryAction.value = 'return';
    } else {
        deliveryMessage.innerHTML =
            '⏰ ¿Deseas marcar el trabajo de este estudiante como <strong>entregado</strong>?<br>' +
            'Esto guardará el tiempo actual del cronómetro.';
        btnSubmitWork.style.display = 'inline-block';
        btnReturnWork.style.display = 'none';
        deliveryAction.value = 'submit';
    }

    deliveryModal.style.display = 'flex';
}

function closeDeliveryModal() {
    if (deliveryModal) deliveryModal.style.display = 'none';
}

// "Devolver Trabajo" — dispara el submit del form via AJAX
function submitReturn() {
    deliveryAction.value = 'return';
    deliveryForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
}

// ============================================================
// Handler AJAX genérico para formularios con modal
// (Un solo listener por form, registrado en DOMContentLoaded)
// ============================================================

function registerAjaxForm(form, onSuccess) {
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Capturar todos los campos del form en FormData
        const formData = new FormData(form);

        // Consolidar tiempo (min:seg → duration_seconds) si aplica
        const minsEl = form.querySelector('#modalMinutes');
        const secsEl = form.querySelector('#modalSeconds');
        const durationInput = form.querySelector('#modalDurationInput');
        if (minsEl && secsEl && durationInput) {
            const totalSecs = ((parseInt(minsEl.value) || 0) * 60) + (parseInt(secsEl.value) || 0);
            durationInput.value = totalSecs;
            formData.set('duration_seconds', totalSecs);
        }

        const url = form.getAttribute('action');
        if (!url) {
            console.error('Form sin action:', form.id);
            return;
        }

        ajaxPost(url, formData,
            () => { onSuccess(); fetchData(); },
            (msg, status) => alert(`Error (${status}): ${msg}`)
        );
    });
}

document.addEventListener('DOMContentLoaded', () => {
    registerAjaxForm(document.getElementById('scoreForm'),    closeScoreModal);
    registerAjaxForm(document.getElementById('deliveryForm'), closeDeliveryModal);

    // Búsqueda de estudiantes en el modal Add Student
    const searchInput  = document.getElementById('studentSearchInput');
    const studentSelect = document.getElementById('studentSelect');
    const noStudents   = document.getElementById('noStudentsFound');

    if (searchInput && studentSelect) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.toLowerCase();
            let visibleCount = 0;
            Array.from(studentSelect.options).forEach(opt => {
                const match = opt.text.toLowerCase().includes(q);
                opt.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });
            if (noStudents) noStudents.style.display = visibleCount === 0 ? 'block' : 'none';
        });
    }
});

// ============================================================
// WhatsApp Share
// ============================================================

function shareOnWhatsApp() {
    const codeEl = document.getElementById('challengeCode');
    if (!codeEl) { alert('No se encontró el código del desafío.'); return; }

    const code = codeEl.textContent.trim();
    if (!code)  { alert('El código está vacío.'); return; }

    const msg = `¡Únete a mi desafío en Classroom Clash! 🚀\n\nCódigo: *${code}*\n\n${window.location.origin}`;
    const url = `https://wa.me/?text=${encodeURIComponent(msg)}`;

    const win = window.open(url, '_blank');
    if (!win || win.closed || typeof win.closed === 'undefined') {
        window.location.href = url;
    }
}

// ============================================================
// Ruleta
// ============================================================

let rouletteParticipants = [];
let selectedWinner       = null;

function openRouletteModal() {
    fetch(`/challenge/${challengeId}/data`)
        .then(r => r.json())
        .then(data => {
            rouletteParticipants = data.participants;
            renderParticipantsList();
            document.getElementById('rouletteModal').style.display = 'flex';
            showRouletteStep(1);
        });
}

function closeRouletteModal() {
    document.getElementById('rouletteModal').style.display = 'none';
    resetRoulette();
}

function renderParticipantsList() {
    const container = document.getElementById('participantsList');
    container.innerHTML = '';
    rouletteParticipants.forEach(p => {
        const div  = document.createElement('div');
        div.className = 'participant-checkbox';
        const cb   = document.createElement('input');
        cb.type    = 'checkbox';
        cb.id      = `roulette-participant-${p.id}`;
        cb.value   = p.id;
        cb.checked = !p.participated;
        const label = document.createElement('label');
        label.htmlFor   = cb.id;
        label.textContent = `${p.name}${p.participated ? ' (Ya participó)' : ''}`;
        div.append(cb, label);
        container.appendChild(div);
    });
}

function selectAllParticipants()   { document.querySelectorAll('#participantsList input[type="checkbox"]').forEach(cb => cb.checked = true); }
function deselectAllParticipants() { document.querySelectorAll('#participantsList input[type="checkbox"]').forEach(cb => cb.checked = false); }

function getSelectedParticipants() {
    return Array.from(document.querySelectorAll('#participantsList input[type="checkbox"]:checked'))
        .map(cb => rouletteParticipants.find(p => p.id == cb.value))
        .filter(Boolean);
}

function spinRoulette() {
    const selected = getSelectedParticipants();
    if (!selected.length) { alert('Selecciona al menos un participante'); return; }

    showRouletteStep(2);
    const display  = document.getElementById('spinnerDisplay');
    let counter    = 0;
    const duration = 3000;

    const interval = setInterval(() => {
        display.textContent = selected[Math.floor(Math.random() * selected.length)].name;
        counter += 100;
        if (counter >= duration) {
            clearInterval(interval);
            selectedWinner = selected[Math.floor(Math.random() * selected.length)];
            showWinner();
        }
    }, 100);
}

function showWinner() {
    document.getElementById('winnerName').textContent = selectedWinner.name;
    document.getElementById('customRoulettePoints').value = '';
    const pts = document.getElementById('winnerCurrentPoints');
    pts.textContent  = `Puntos actuales: ${selectedWinner.points} pts`;
    pts.style.color  = '#6b7280';
    pts.style.fontWeight = 'normal';
    showRouletteStep(3);
}

function assignRoulettePoints(points) {
    if (!selectedWinner) return;
    if ((parseInt(selectedWinner.points) || 0) + points > maxPoints) {
        alert(`Límite de puntos: ${maxPoints}`); return;
    }
    sendRoulettePoints(selectedWinner.id, points);
}

function assignCustomRoulettePoints() {
    const pts = parseInt(document.getElementById('customRoulettePoints').value);
    if (!pts || pts < 1) { alert('Ingresa una cantidad válida'); return; }
    assignRoulettePoints(pts);
}

function sendRoulettePoints(participantId, points) {
    ajaxPost(`/challenge/${challengeId}/roulette`, { participant_id: participantId, points },
        data => {
            const pts = document.getElementById('winnerCurrentPoints');
            pts.textContent  = `Puntos actuales: ${data.participant.points} pts`;
            pts.style.color  = '#10b981';
            pts.style.fontWeight = 'bold';
            fetchData();
            const idx = rouletteParticipants.findIndex(p => p.id === participantId);
            if (idx !== -1) {
                rouletteParticipants[idx].participated = true;
                rouletteParticipants[idx].points = data.participant.points;
            }
        }
    );
}

function resetRoulette() { selectedWinner = null; showRouletteStep(1); renderParticipantsList(); }

function showRouletteStep(step) {
    [1, 2, 3].forEach(n => {
        document.getElementById(`rouletteStep${n}`).style.display = step === n ? 'block' : 'none';
    });
}

// ============================================================
// Equipos
// ============================================================

function openTeamsModal()  { document.getElementById('teamsModal').style.display = 'flex'; }
function closeTeamsModal() { document.getElementById('teamsModal').style.display = 'none'; }

function adjustTeamSize(delta) {
    const input = document.getElementById('teamSizeInput');
    const val   = parseInt(input.value) + delta;
    if (val >= 2 && val <= 10) input.value = val;
}

// ============================================================
// Modal Añadir Estudiante
// ============================================================

function openAddStudentModal()  { document.getElementById('addStudentModal').style.display = 'flex'; }
function closeAddStudentModal() { document.getElementById('addStudentModal').style.display = 'none'; }

// ============================================================
// Cerrar modales al hacer clic en el fondo
// ============================================================

window.onclick = function (event) {
    const modals = {
        scoreModal:       closeScoreModal,
        deliveryModal:    closeDeliveryModal,
        rouletteModal:    closeRouletteModal,
        teamsModal:       closeTeamsModal,
        addStudentModal:  closeAddStudentModal,
    };
    Object.entries(modals).forEach(([id, closeFn]) => {
        const el = document.getElementById(id);
        if (el && event.target === el) closeFn();
    });
};

// ============================================================
// Exponer al scope global (para onclick en el HTML)
// ============================================================

Object.assign(window, {
    shareOnWhatsApp,
    openScoreModal, closeScoreModal,
    adjustModalScore, applySuggestedPoints,
    adjustModalTime, setPenalty, addModalPenalty, subtractModalTime,
    openDeliveryModal, closeDeliveryModal, submitReturn,
    openRouletteModal, closeRouletteModal,
    selectAllParticipants, deselectAllParticipants,
    spinRoulette, assignRoulettePoints, assignCustomRoulettePoints, resetRoulette,
    openTeamsModal, closeTeamsModal, adjustTeamSize,
    openAddStudentModal, closeAddStudentModal,
});
