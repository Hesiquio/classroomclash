// Challenge Show Page Logic

// State variables initialized from config
let seconds = window.ChallengeConfig.seconds;
let isRunning = window.ChallengeConfig.isRunning;
const { challengeId, isDocente, userId, minPoints, maxPoints } = window.ChallengeConfig;
const timerElement = document.getElementById('timer');
let currentParticipantPosition = 0;
let totalParticipants = 0;
let suggestedPoints = 0;

// WhatsApp Share functionality
function shareOnWhatsApp() {
    const codeElement = document.getElementById('challengeCode');
    if (!codeElement) {
        alert('Error: No se encontró el código del desafío.');
        return;
    }

    const codeText = codeElement.textContent.trim();
    if (!codeText) {
        alert('Error: El código está vacío.');
        return;
    }

    const message = `¡Únete a mi desafío en Classroom Clash! 🚀\n\nCódigo de acceso: *${codeText}*\n\nIngresa aquí: ${window.location.origin}`;
    const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;

    // Try opening in new tab
    const newWindow = window.open(whatsappUrl, '_blank');

    // Check if blocked
    if (!newWindow || newWindow.closed || typeof newWindow.closed == 'undefined') {
        alert('Por favor habilita las ventanas emergentes para compartir en WhatsApp.');
        // Fallback to current window
        window.location.href = whatsappUrl;
    }
}

function formatTime(totalSeconds) {
    const h = Math.floor(totalSeconds / 3600).toString().padStart(2, '0');
    const m = Math.floor((totalSeconds % 3600) / 60).toString().padStart(2, '0');
    const s = (totalSeconds % 60).toString().padStart(2, '0');
    return `${h}:${m}:${s}`;
}

function calculateSuggestedPoints(position, total) {
    if (total <= 1) return maxPoints;
    const pointsRange = maxPoints - minPoints;
    const positionFactor = (total - position) / (total - 1);
    return Math.round(minPoints + (pointsRange * positionFactor));
}

let wasRunning = window.ChallengeConfig.isRunning;

setInterval(() => {
    if (isRunning) {
        seconds++;
        if (timerElement) timerElement.textContent = formatTime(seconds);
    }

    // Detectar cuando el desafío se acaba de finalizar
    if (wasRunning && !isRunning) {
        handleChallengeFinalized();
    }
    wasRunning = isRunning;
}, 1000);

/**
 * Se llama UNA vez cuando isRunning pasa de true → false (desafío finalizado).
 * Inyecta el tiempo global actual en las tarjetas que aún no muestran tiempo.
 */
function handleChallengeFinalized() {
    // Mostrar banner de finalización si aún no existe
    if (!document.getElementById('finalizedBanner')) {
        const banner = document.createElement('div');
        banner.id = 'finalizedBanner';
        banner.style.cssText = [
            'position:fixed', 'top:1rem', 'left:50%',
            'transform:translateX(-50%)', 'z-index:9999',
            'background:linear-gradient(135deg,#1e293b,#334155)',
            'color:white', 'padding:0.75rem 1.5rem',
            'border-radius:10px', 'font-weight:700',
            'box-shadow:0 4px 20px rgba(0,0,0,0.3)',
            'font-size:1rem', 'letter-spacing:0.02em',
            'animation:fadeIn 0.4s ease'
        ].join(';');
        banner.textContent = '⏹️ Desafío finalizado — ' + formatTime(seconds);
        document.body.appendChild(banner);
        setTimeout(() => banner.remove(), 5000);
    }

    // Inyectar tiempo estimado en tarjetas sin tiempo registrado
    document.querySelectorAll('.participant-card:not(.placeholder)').forEach(card => {
        const infoDiv = card.querySelector('.student-info');
        if (!infoDiv) return;
        if (!infoDiv.querySelector('.submission-time')) {
            const timeDiv = document.createElement('div');
            timeDiv.className = 'submission-time submission-time-estimated';
            timeDiv.title = 'Tiempo estimado al finalizar el desafío';
            timeDiv.textContent = `⏱ ~${formatTime(seconds)}`;
            infoDiv.appendChild(timeDiv);
        }
    });
}

function fetchData() {
    fetch(`/challenge/${challengeId}/data`)
        .then(response => response.json())
        .then(data => {
            seconds = data.challenge.current_time_seconds;
            isRunning = data.challenge.is_running;
            if (timerElement) timerElement.textContent = formatTime(seconds);
            updateParticipantsGrid(data.participants);
        })
        .catch(() => { });
}

function updateParticipantsGrid(participants) {
    const scoreModal = document.getElementById('scoreModal');
    const deliveryModal = document.getElementById('deliveryModal');

    if (scoreModal && scoreModal.style.display === 'flex') return;
    if (deliveryModal && deliveryModal.style.display === 'flex') return;

    participants.forEach((p, index) => {
        let card = document.getElementById(`participant-card-${p.id}`);

        if (!card) {
            if (participants.length !== document.querySelectorAll('.participant-card:not(.placeholder)').length) {
                location.reload();
                return;
            }
        } else {
            // Update Rank Styling (preserve current-user class)
            let classes = `participant-card ${index < 3 ? 'top-rank rank-' + (index + 1) : ''}`;
            if (p.user_id === userId) {
                classes += ' current-user';
            }
            card.className = classes;

            const badge = card.querySelector('.position-badge');
            if (index === 0) badge.innerHTML = '<span class="medal">🥇</span>';
            else if (index === 1) badge.innerHTML = '<span class="medal">🥈</span>';
            else if (index === 2) badge.innerHTML = '<span class="medal">🥉</span>';
            else badge.innerHTML = `<span class="rank-number">#${index + 1}</span>`;

            card.querySelector('.points-value').textContent = p.points;

            if (isDocente) {
                const settingsBtn = card.querySelector('.btn-icon.settings');
                if (settingsBtn) {
                    settingsBtn.setAttribute('onclick', `openScoreModal(${p.id}, '${p.name}', ${p.points}, ${p.duration_seconds || 0}, ${p.finished_at ? 'true' : 'false'})`);
                }

                const actionsDiv = card.querySelector('.card-actions');
                let deliveryBtn = card.querySelector('.btn-icon.validate'); // We kept the class 'validate' for styling

                if (!deliveryBtn) {
                    deliveryBtn = document.createElement('button');
                    deliveryBtn.type = 'button';
                    deliveryBtn.className = 'btn-icon validate';
                    actionsDiv.appendChild(deliveryBtn);
                }

                // Update button state
                if (p.finished_at) {
                    deliveryBtn.innerHTML = '🔄';
                    deliveryBtn.title = 'Devolver Trabajo';
                    deliveryBtn.classList.add('validated');
                    deliveryBtn.setAttribute('onclick', `openDeliveryModal(${p.id}, '${p.name}', true)`);
                } else {
                    deliveryBtn.innerHTML = '⏰';
                    deliveryBtn.title = 'Entregar Trabajo';
                    deliveryBtn.classList.remove('validated');
                    deliveryBtn.setAttribute('onclick', `openDeliveryModal(${p.id}, '${p.name}', false)`);
                }
            }

            const infoDiv = card.querySelector('.student-info');
            let timeDiv = infoDiv.querySelector('.submission-time');
            if (p.finished_at) {
                if (!timeDiv) {
                    timeDiv = document.createElement('div');
                    timeDiv.className = 'submission-time';
                    infoDiv.appendChild(timeDiv);
                }
                timeDiv.textContent = `⏱ ${p.formatted_time}`;
            }
        }
    });
}

setInterval(fetchData, 3000);

const modal = document.getElementById('scoreModal');
const modalName = document.getElementById('modalStudentName');
const modalInput = document.getElementById('modalPointsInput');
if (modalInput) {
    modalInput.addEventListener('input', function () {
        let val = parseInt(this.value);
        if (val > maxPoints) this.value = maxPoints;
        if (val < 0) this.value = 0;
    });
}
const modalForm = document.getElementById('scoreForm');

function openScoreModal(participantId, name, points, durationSeconds, hasFinished) {
    modalName.textContent = '⚙️ Ajustar: ' + name;
    modalInput.value = points;
    modalForm.action = `/challenge/${challengeId}/participant/${participantId}/score`;

    const deleteForm = document.getElementById('deleteParticipantForm');
    if (deleteForm) {
        deleteForm.action = `/challenge/${challengeId}/participant/${participantId}/delete`;
    }

    // Siempre mostrar la sección de tiempo
    const timeSection = document.getElementById('timeAdjustSection');
    const timeSectionTitle = document.getElementById('timeSectionTitle');
    const modalMinutes = document.getElementById('modalMinutes');
    const modalSeconds = document.getElementById('modalSeconds');

    timeSection.style.display = 'block';

    if (hasFinished && durationSeconds !== undefined && durationSeconds > 0) {
        // Estudiante ya entregó: usar su tiempo registrado
        const mins = Math.floor(durationSeconds / 60);
        const secs = durationSeconds % 60;
        modalMinutes.value = mins;
        modalSeconds.value = secs;
        if (timeSectionTitle) timeSectionTitle.textContent = '⏱️ Tiempo Final';
    } else {
        // Estudiante aún no entregó: pre-cargar con el tiempo actual del cronómetro
        const currentMins = Math.floor(seconds / 60);
        const currentSecs = seconds % 60;
        modalMinutes.value = currentMins;
        modalSeconds.value = currentSecs;
        if (timeSectionTitle) timeSectionTitle.textContent = '⏱️ Tiempo de Actividad';
    }

    // Find participant position
    const participantCards = document.querySelectorAll('.participant-card:not(.placeholder)');
    totalParticipants = participantCards.length;

    participantCards.forEach((card, index) => {
        if (card.id === `participant-card-${participantId}`) {
            currentParticipantPosition = index + 1;
        }
    });

    // Calculate and show suggested points
    suggestedPoints = calculateSuggestedPoints(currentParticipantPosition, totalParticipants);
    document.getElementById('suggestedPointsValue').textContent = suggestedPoints;
    document.getElementById('suggestedPointsInfo').style.display = 'block';

    modal.style.display = 'flex';
}

function closeScoreModal() {
    if (modal) modal.style.display = 'none';
    const info = document.getElementById('suggestedPointsInfo');
    if (info) info.style.display = 'none';
}

function adjustModalScore(amount) {
    let current = parseInt(modalInput.value) || 0;
    let newVal = current + amount;
    if (newVal < 0) newVal = 0;
    if (newVal > maxPoints) newVal = maxPoints;
    modalInput.value = newVal;
}

function applySuggestedPoints() {
    modalInput.value = suggestedPoints;
}

// Ajustar tiempo del modal (penalización positiva o reducción negativa)
function setPenalty(penaltySecs) {
    const modalMinutes = document.getElementById('modalMinutes');
    const modalSeconds = document.getElementById('modalSeconds');

    let mins = parseInt(modalMinutes.value) || 0;
    let secs = parseInt(modalSeconds.value) || 0;

    let totalSecs = (mins * 60) + secs + penaltySecs;
    if (totalSecs < 0) totalSecs = 0; // No permitir tiempo negativo

    modalMinutes.value = Math.floor(totalSecs / 60);
    modalSeconds.value = totalSecs % 60;
}

// Alias para compatibilidad con botones del HTML que llaman addModalPenalty
function addModalPenalty(penaltySecs) {
    setPenalty(penaltySecs);
}

// Ajustar tiempo: restar segundos (para reducir el tiempo)
function subtractModalTime(penaltySecs) {
    setPenalty(-penaltySecs);
}

// Handle score form submission to include time
if (modalForm) {
    modalForm.addEventListener('submit', function (e) {
        const modalDurationInput = document.getElementById('modalDurationInput');
        const modalMinutes = document.getElementById('modalMinutes');
        const modalSeconds = document.getElementById('modalSeconds');

        if (modalMinutes && modalSeconds && modalMinutes.value !== '' && modalSeconds.value !== '') {
            const mins = parseInt(modalMinutes.value) || 0;
            const secs = parseInt(modalSeconds.value) || 0;
            modalDurationInput.value = (mins * 60) + secs;
        }
    });
}

const deliveryModal = document.getElementById('deliveryModal');
const deliveryModalName = document.getElementById('deliveryModalStudentName');
const deliveryForm = document.getElementById('deliveryForm');
const deliveryAction = document.getElementById('deliveryAction');
const btnSubmitWork = document.getElementById('btnSubmitWork');
const btnReturnWork = document.getElementById('btnReturnWork');
const deliveryMessage = document.getElementById('deliveryMessage');

function openDeliveryModal(participantId, name, hasFinished) {
    deliveryModalName.textContent = hasFinished ? `Devolver Trabajo: ${name}` : `Entregar Trabajo: ${name}`;
    deliveryForm.action = `/challenge/${challengeId}/participant/${participantId}/validate`;

    // Set message and buttons based on delivery status
    if (hasFinished) {
        deliveryMessage.innerHTML = '🔄 Este estudiante ya <strong>entregó su trabajo</strong>.<br>¿Deseas <strong>devolverlo</strong> para que continúe trabajando?';
        btnSubmitWork.style.display = 'none';
        btnReturnWork.style.display = 'inline-block';
        deliveryAction.value = 'return';
    } else {
        deliveryMessage.innerHTML = '⏰ ¿Deseas marcar el trabajo de este estudiante como <strong>entregado</strong>?<br>Esto detendrá su cronómetro y guardará el tiempo actual.';
        btnSubmitWork.style.display = 'inline-block';
        btnReturnWork.style.display = 'none';
        deliveryAction.value = 'submit';
    }

    deliveryModal.style.display = 'flex';
}

function closeDeliveryModal() {
    if (deliveryModal) deliveryModal.style.display = 'none';
}

function submitReturn() {
    deliveryAction.value = 'return';
    // Disparar el evento submit del form para que lo maneje handleAjaxForm via AJAX
    deliveryForm.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
}

function handleAjaxForm(form, callback) {
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(form);

        // getAttribute evita la colisión con inputs que tengan name="action"
        const actionUrl = form.getAttribute('action');
        fetch(actionUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
            .then(response => {
                // Intentar leer el JSON siempre, incluso en errores 4xx/5xx
                return response.json().then(data => ({ ok: response.ok, status: response.status, data }));
            })
            .then(({ ok, status, data }) => {
                if (ok && data.success) {
                    callback();
                    fetchData();
                } else {
                    // Mostrar error real del servidor si está disponible
                    let errorMsg = 'Hubo un error al guardar. Por favor intenta de nuevo.';
                    if (data.message) {
                        errorMsg = data.message;
                    } else if (data.errors) {
                        const firstError = Object.values(data.errors)[0];
                        errorMsg = Array.isArray(firstError) ? firstError[0] : firstError;
                    }
                    alert(`Error (${status}): ${errorMsg}`);
                }
            })
            .catch(error => {
                console.error('AJAX error:', error);
                alert('Error de red. Verifica tu conexión e intenta de nuevo.');
            });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const modalForm = document.getElementById('scoreForm');
    const deliveryForm = document.getElementById('deliveryForm');

    if (modalForm) {
        handleAjaxForm(modalForm, closeScoreModal);
    }

    if (deliveryForm) {
        handleAjaxForm(deliveryForm, closeDeliveryModal);
    }
});

// Roulette functionality
let rouletteParticipants = [];
let selectedWinner = null;

function openRouletteModal() {
    // Fetch current participants
    fetch(`/challenge/${challengeId}/data`)
        .then(response => response.json())
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
        const div = document.createElement('div');
        div.className = 'participant-checkbox';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = `roulette-participant-${p.id}`;
        checkbox.value = p.id;
        checkbox.checked = !p.participated; // Auto-check if not participated

        const label = document.createElement('label');
        label.htmlFor = `roulette-participant-${p.id}`;
        label.textContent = `${p.name} ${p.participated ? '(Ya participó)' : ''}`;

        div.appendChild(checkbox);
        div.appendChild(label);
        container.appendChild(div);
    });
}

function selectAllParticipants() {
    document.querySelectorAll('#participantsList input[type="checkbox"]').forEach(cb => cb.checked = true);
}

function deselectAllParticipants() {
    document.querySelectorAll('#participantsList input[type="checkbox"]').forEach(cb => cb.checked = false);
}

function getSelectedParticipants() {
    const selected = [];
    document.querySelectorAll('#participantsList input[type="checkbox"]:checked').forEach(cb => {
        const participant = rouletteParticipants.find(p => p.id == cb.value);
        if (participant) selected.push(participant);
    });
    return selected;
}

function spinRoulette() {
    const selected = getSelectedParticipants();

    if (selected.length === 0) {
        alert('Debes seleccionar al menos un participante');
        return;
    }

    showRouletteStep(2);

    // Spinning animation
    const spinnerDisplay = document.getElementById('spinnerDisplay');
    let counter = 0;
    const spinDuration = 3000; // 3 seconds
    const interval = 100; // Change name every 100ms

    const spinInterval = setInterval(() => {
        const randomIndex = Math.floor(Math.random() * selected.length);
        spinnerDisplay.textContent = selected[randomIndex].name;
        counter += interval;

        if (counter >= spinDuration) {
            clearInterval(spinInterval);
            // Select final winner
            const winnerIndex = Math.floor(Math.random() * selected.length);
            selectedWinner = selected[winnerIndex];
            showWinner();
        }
    }, interval);
}

function showWinner() {
    document.getElementById('winnerName').textContent = selectedWinner.name;
    document.getElementById('customRoulettePoints').value = '';
    // Show current points
    const pointsDisplay = document.getElementById('winnerCurrentPoints');
    pointsDisplay.textContent = `Puntos actuales: ${selectedWinner.points} pts`;
    pointsDisplay.style.fontWeight = 'normal';
    pointsDisplay.style.color = '#6b7280';
    showRouletteStep(3);
}

function assignRoulettePoints(points) {
    if (!selectedWinner) return;

    const currentPoints = parseInt(selectedWinner.points) || 0;
    if (currentPoints + points > maxPoints) {
        alert(`No puedes asignar más puntos. El límite es ${maxPoints}.`);
        return;
    }

    sendRoulettePoints(selectedWinner.id, points);
}

function assignCustomRoulettePoints() {
    const points = parseInt(document.getElementById('customRoulettePoints').value);

    if (!points || points < 1) {
        alert('Ingresa una cantidad válida de puntos');
        return;
    }

    assignRoulettePoints(points);
}

function sendRoulettePoints(participantId, points) {
    const formData = new FormData();
    formData.append('participant_id', participantId);
    formData.append('points', points);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

    fetch(`/challenge/${challengeId}/roulette`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the current points display
                const pointsDisplay = document.getElementById('winnerCurrentPoints');
                pointsDisplay.textContent = `Puntos actuales: ${data.participant.points} pts`;
                pointsDisplay.style.fontWeight = 'bold';
                pointsDisplay.style.color = '#10b981';

                fetchData(); // Refresh main grid
                // Update roulette participants list
                const pIndex = rouletteParticipants.findIndex(p => p.id === participantId);
                if (pIndex !== -1) {
                    rouletteParticipants[pIndex].participated = true;
                    rouletteParticipants[pIndex].points = data.participant.points;
                }
            }
        })
        .catch(error => {
            alert('Hubo un error al asignar puntos');
        });
}

function resetRoulette() {
    selectedWinner = null;
    showRouletteStep(1);
    renderParticipantsList();
}

function showRouletteStep(step) {
    document.getElementById('rouletteStep1').style.display = step === 1 ? 'block' : 'none';
    document.getElementById('rouletteStep2').style.display = step === 2 ? 'block' : 'none';
    document.getElementById('rouletteStep3').style.display = step === 3 ? 'block' : 'none';
}

// Expose functions to window for HTML onclick attributes
window.shareOnWhatsApp = shareOnWhatsApp;
window.openScoreModal = openScoreModal;
window.closeScoreModal = closeScoreModal;
window.adjustModalScore = adjustModalScore;
window.applySuggestedPoints = applySuggestedPoints;
window.openDeliveryModal = openDeliveryModal;
window.closeDeliveryModal = closeDeliveryModal;
window.setPenalty = setPenalty;
window.addModalPenalty = addModalPenalty;
window.subtractModalTime = subtractModalTime;
window.openRouletteModal = openRouletteModal;
window.closeRouletteModal = closeRouletteModal;
window.selectAllParticipants = selectAllParticipants;
window.deselectAllParticipants = deselectAllParticipants;
window.spinRoulette = spinRoulette;
window.assignRoulettePoints = assignRoulettePoints;
window.assignCustomRoulettePoints = assignCustomRoulettePoints;
window.resetRoulette = resetRoulette;

/* Teams Modal Functions */

function openTeamsModal() {
    document.getElementById('teamsModal').style.display = 'flex';
}

function closeTeamsModal() {
    document.getElementById('teamsModal').style.display = 'none';
}

function adjustTeamSize(change) {
    const input = document.getElementById('teamSizeInput');
    let newValue = parseInt(input.value) + change;
    if (newValue >= 2 && newValue <= 10) {
        input.value = newValue;
    }
}

/* Add Student Modal Functions */
function openAddStudentModal() {
    document.getElementById('addStudentModal').style.display = 'flex';
}

function closeAddStudentModal() {
    document.getElementById('addStudentModal').style.display = 'none';
}

// Expose functions to window
window.openTeamsModal = openTeamsModal;
window.closeTeamsModal = closeTeamsModal;
window.adjustTeamSize = adjustTeamSize;
window.openAddStudentModal = openAddStudentModal;
window.closeAddStudentModal = closeAddStudentModal;

// Consolidated window.onclick handler for all modals
window.onclick = function (event) {
    const scoreModal = document.getElementById('scoreModal');
    const deliveryModal = document.getElementById('deliveryModal');
    const rouletteModal = document.getElementById('rouletteModal');
    const teamsModal = document.getElementById('teamsModal');
    const addStudentModal = document.getElementById('addStudentModal');

    if (event.target == scoreModal) closeScoreModal();
    if (event.target == deliveryModal) closeDeliveryModal();
    if (event.target == rouletteModal) closeRouletteModal();
    if (event.target == teamsModal) closeTeamsModal();
    if (event.target == addStudentModal) closeAddStudentModal();
}
